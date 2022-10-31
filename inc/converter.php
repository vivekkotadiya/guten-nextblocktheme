<?php
if (!defined('ABSPATH')) {
    die;
}

function debug($info)
{
    $message = null;

    if (is_string($info) || is_int($info) || is_float($info)) {
        $message = $info;
    } else {
        $message = var_export($info, true);
    }

    if ($fh = fopen(ABSPATH . '/gdwebpconvert.log', 'a')) {
        fputs($fh, date('Y-m-d H:i:s') . " $message\n");
        fclose($fh);
    }
}

add_filter('wp_generate_attachment_metadata', 'gd_webp_converter', 10, 2);

function gd_webp_converter($metadata, $attachment_id)
{  

    if(!empty($metadata)){
     
        $gd_webp_converter = new GDWebPConverter($attachment_id);

        $fileDirectory = $gd_webp_converter->file_dirname;
        $mainfile =  $metadata['file'];
        if(!empty($mainfile)){
            $fileName = pathinfo($mainfile, PATHINFO_FILENAME);
            $fileextenstion = $gd_webp_converter->file_ext;
            $allowed_mime_ext = array('jpeg', 'png', 'jpg');
            
            if (in_array($fileextenstion, $allowed_mime_ext, true)) {
                $gd_webp_converter->check_file_exists($attachment_id);
                $gd_webp_converter->check_mime_type();
                $gd_webp_converter->create_array_of_sizes_to_be_converted($metadata);
                $gd_webp_converter->image_optim();
                $gd_webp_converter->convert_array_of_sizes($attachment_id);

            
                    $sizes = $metadata['sizes'];
                    if(!empty($sizes)){
                        $webparray = array();

                        if (file_exists($fileDirectory. '/' . $fileName. '.webp')) {
                            $file_size = filesize($fileDirectory. '/' . $fileName. '.webp');
                            $webparray['main_webp'] = array (
                                'file' => $fileName. '.webp',
                                'width' => $metadata['width'],
                                'height' => $metadata['height'],
                                'mime-type' => 'image/webp',
                                'filesize' => $file_size
                            );
                        }
                
                        foreach ($sizes as $size => $filedata){
                            $fileName = pathinfo($filedata['file'], PATHINFO_FILENAME);
                            if (file_exists($fileDirectory. '/' . $fileName. '.webp')) {
                                $file_size = filesize($fileDirectory. '/' . $fileName. '.webp');
                                $webparray[$size.'_webp'] = array (
                                    'file' => $fileName. '.webp',
                                    'width' => $filedata['width'],
                                    'height' => $filedata['height'],
                                    'mime-type' => 'image/webp',
                                    'filesize' => $file_size
                                );
                            }
                        }

                        if(!empty($webparray)){
                            $sizes = array_merge($sizes,$webparray);
                            $metadata['sizes'] = $sizes;
                        }
                    }
            
            }
        }
    }
   
    return $metadata;
}

class GDWebPConverter
{

    private $file_path;
    public $file_dirname;
    public $file_ext;
    private $file_name_no_ext;

    private $array_of_sizes_to_be_converted = array();
    private $array_of_sizes_to_be_process   = array();
 

    public function __construct($attachment_id)
    {

        $this->file_path = get_attached_file($attachment_id);
      
        $this->file_dirname = pathinfo($this->file_path, PATHINFO_DIRNAME);

        $this->file_ext = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));

        $this->file_name_no_ext = pathinfo($this->file_path, PATHINFO_FILENAME);

   
    }

    public function check_file_exists($attachment_id)
    {

        $file = get_attached_file($attachment_id);

        if (!file_exists($file)) {
            $message = 'The uploaded file does not exist on the server. Encoding not possible.';
            throw new Exception('The uploaded file does exist on the server. Encoding not possible.', 1);
        }
    }

    public function check_mime_type()
    {

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $this->file_mime_type = finfo_file($finfo, $this->file_path);

        finfo_close($finfo);

        $this->allowed_mime_type = array('image/jpeg', 'image/png');

        if (!in_array($this->file_mime_type, $this->allowed_mime_type, true)) {

            $message = 'MIME type of file not supported';
            throw new Exception('MIME type of file not supported', 1);
        }
    }

    public function create_array_of_sizes_to_be_converted($metadata)
    {

        // push original file to the array
        array_push($this->array_of_sizes_to_be_converted, $this->file_path);

        // push all created sizes of the file to the array
        foreach ($metadata['sizes'] as $value) {
            array_push($this->array_of_sizes_to_be_converted, $this->file_dirname . '/' . $value['file']);
        }
    }

    public function image_optim()
    {

        switch ($this->file_ext) {
            case 'jpeg':
            case 'jpg':

                foreach ($this->array_of_sizes_to_be_converted as $key => $value) {

                    if (0 === $key) {
                        if (!is_executable('/usr/local/bin/jpegoptim')) {
                            //trigger_error('`/usr/local/bin/jpegoptim` is not executable, please install it.');
                            return;
                        }
                        if (is_file($value)) {

                            $cmd    = '/usr/local/bin/jpegoptim -m%d --strip-all %s 2>&1';
                            $result = exec(sprintf($cmd, intval(86), escapeshellarg($value)), $output, $status);
                            if ($status) {
                                //trigger_error($result);
                            }
                        }
                    }
                }
                break;

            case 'png':

                foreach ($this->array_of_sizes_to_be_converted as $key => $value) {
                    if (0 === $key) {
                        if (!is_executable('/usr/local/bin/optipng')) {
                            //trigger_error('`/usr/local/bin/optipng` is not executable, please install it.');
                            return;
                        }
                        if (is_file($value)) {
                            $cmd    = '/usr/local/bin/optipng %s 2>&1';
                            $result = exec(sprintf($cmd, escapeshellarg($value)), $output, $status);
                            if ($status) {
                                //trigger_error($result);
                            }
                        }
                    }
                }
                break;

            default:
                return false;
        }
    }

    public function convert_array_of_sizes($attachment_id)
    {

        if (is_executable('/usr/local/bin/cwebp')) {

            foreach ($this->array_of_sizes_to_be_converted as $key => $value) {

                if (0 === $key) {

                    $path = $this->file_dirname . '/' . $this->file_name_no_ext . '.webp';
                    $result    = exec('cwebp -q 86 ' . $value . ' -o ' . $this->file_dirname . '/' . $this->file_name_no_ext . '.webp', $output, $status);

                } else {

                    $current_size = getimagesize($value);
                    $path = $this->file_dirname . '/' . $this->file_name_no_ext . '-' . $current_size[0] . 'x' . $current_size[1] . '.webp';
                    $result    = exec('cwebp -q 86 ' . $value . ' -o ' . $this->file_dirname . '/' . $this->file_name_no_ext . '-' . $current_size[0] . 'x' . $current_size[1] . '.webp', $output, $status);
                }
            }
        } else {

            switch ($this->file_ext) {

                case 'jpeg':
                case 'jpg':

                    foreach ($this->array_of_sizes_to_be_converted as $key => $value) {

                        $image = imagecreatefromjpeg($value);

                        if (0 === $key) {

                            imagewebp($image, $this->file_dirname . '/' . $this->file_name_no_ext . '.webp', 86);

                            // debug(get_attached_file($attachment_id));
                        } else {

                            $current_size = getimagesize($value);
                            imagewebp($image, $this->file_dirname . '/' . $this->file_name_no_ext . '-' . $current_size[0] . 'x' . $current_size[1] . '.webp', 86);

                            // debug(get_attached_file($attachment_id));
                        }

                        imagedestroy($image);
                    }
                    break;

                case 'png':

                    foreach ($this->array_of_sizes_to_be_converted as $key => $value) {

                        $image = imagecreatefrompng($value);
                        imagepalettetotruecolor($image);
                        imagealphablending($image, true);
                        imagesavealpha($image, true);

                        if (0 === $key) {

                            imagewebp($image, $this->file_dirname . '/' . $this->file_name_no_ext . '.webp', 86);
                        } else {

                            $current_size = getimagesize($value);

                            imagewebp($image, $this->file_dirname . '/' . $this->file_name_no_ext . '-' . $current_size[0] . 'x' . $current_size[1] . '.webp', 86);
                        }

                        imagedestroy($image);
                    }
                    break;

                default:
                    return false;
            }
        }
    }

    public function create_array_of_sizes_to_be_process($attachment_id)
    {

        $this->attachment_metadata_of_file_to_be_deleted = wp_get_attachment_metadata($attachment_id);

        if(!empty($this->attachment_metadata_of_file_to_be_deleted) ){

            if(file_exists($this->file_dirname . '/' . $this->file_name_no_ext . '.webp')){

            // push original file to the array
            array_push($this->array_of_sizes_to_be_process, $this->file_dirname . '/' . $this->file_name_no_ext . '.webp');

                if(!empty($this->attachment_metadata_of_file_to_be_deleted['sizes'])){
                    // push all created sizes of the file to the array
                        foreach ($this->attachment_metadata_of_file_to_be_deleted['sizes'] as $value) {

                            $this->value_file_name_no_ext = pathinfo($value['file'], PATHINFO_FILENAME);

                            array_push($this->array_of_sizes_to_be_process, $this->file_dirname . '/' . $this->value_file_name_no_ext . '.webp');
                        }
                    }
            }
        }
    }


}

add_filter('big_image_size_threshold', '__return_false');

/**
 * Remove Default medium and large image size from wordpress
*/

add_filter( 'intermediate_image_sizes_advanced', 'prefix_remove_default_images' );

// This will remove the default image sizes medium and large. 

function prefix_remove_default_images( $sizes ) {

	if(isset($sizes['medium'])){
		unset( $sizes['medium']); // 300px
	}

	if(isset($sizes['large'])){
		unset( $sizes['large']); // 1024px
	}
	if(isset($sizes['medium_large'])){
		unset( $sizes['medium_large']); // 768px
	}
		
	return $sizes;
}



add_action( 'mfrh_path_renamed', 'filter_meta_data_when_rename_called_from_plugin', 10, 3 );

function filter_meta_data_when_rename_called_from_plugin(){

	add_filter( 'update_attached_file', 'hook_into_rename_file_name', 10, 2 );

}

function hook_into_rename_file_name($file, $attachment_id){


	if(!empty($attachment_id)){
	
		$metadata = get_post_meta($attachment_id, '_wp_attachment_metadata', true);
		
		$sizes = $metadata['sizes'];
		$allowed_mime_ext = array('jpeg', 'png', 'jpg');

			if(!empty($sizes)){

				$mainfile = $file;
				$mainfileName = pathinfo($mainfile, PATHINFO_FILENAME);
				$mainfileExtenstion = strtolower(pathinfo($mainfile, PATHINFO_EXTENSION));
			
				if (in_array($mainfileExtenstion, $allowed_mime_ext, true)) {
				
					$webpmainfile = isset($sizes['main_webp']) ? $sizes['main_webp']['file'] : $mainfile;
					$webpfileName = pathinfo($webpmainfile, PATHINFO_FILENAME);
					
					if($webpfileName != $mainfileName){
						foreach ($sizes as $size => $filedata){
							$fileName = pathinfo($filedata['file'], PATHINFO_FILENAME);
							$fileExenstion = strtolower(pathinfo($filedata['file'], PATHINFO_EXTENSION));
							if ($fileExenstion == 'webp') {
								if($size == 'main_webp'){
									$metadata['sizes'][$size]['file'] = $mainfileName .'.webp';
								} else{
									$metadata['sizes'][$size]['file'] = $mainfileName . '-' . $filedata['width'] . 'x' . $filedata['height'] . '.webp';
								}
								
							}
						}
					}

				}

				
				$status = update_post_meta($attachment_id, '_wp_attachment_metadata', $metadata);
							
			}

	}	

	return $file;
}	









