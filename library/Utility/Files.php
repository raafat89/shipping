<?php

class Utility_Files {

    function resizeImg($current_path, $target_path, $max_width, $max_height) {
        // get image size infos (0 width and 1 height, 2 is [1 = GIF, 2 = JPG, 3 = PNG])
        $size = getimagesize($current_path);
        // break and return false if failed to read image infos
        if (!$size) {
            throw new Exception("Failed to read file");
        }

        // relation: width/height
        // maximal size (if parameter == false, no resizing will be made)
        $max_size = array(($max_width > 0) ? $max_width : $size[0], ($max_height > 0) ? $max_height : $size[1]);
        // declaring array for new size (initial value = original size)
        $new_size = $size;
        // width/height relation
        $relation = array($size[1] / $size[0], $size[0] / $size[1]);

        // determine new size
        if ($new_size[0] > $max_size[0]) {
            $new_size[0] = $max_size[0];
            $new_size[1] = floor($new_size[0] * $relation[0]);
        }
        if ($new_size[1] > $max_size[1]) {
            $new_size[1] = $max_size[1];
            $new_size[0] = floor($new_size[1] * $relation[1]);
        }

        // create initial image
        switch ($size[2]) {
            case 1:
                if (function_exists("imagecreatefromgif")) {
                    $image = imagecreatefromgif($current_path);
                } else {
                    throw new Exception("No GIF support installed on the server");
                }
                break;
            case 2:
                if (function_exists("imagecreatefromjpeg")) {
                    $image = imagecreatefromjpeg($current_path);
                } else {
                    throw new Exception("No JPG support installed on the server");
                }
                break;
            case 3:
                if (function_exists("imagecreatefrompng")) {
                    $image = imagecreatefrompng($current_path);
                    imagealphablending($image, true);
                } else {
                    throw new Exception("No PNG support installed on the server");
                }
                break;
            default:
                throw new Exception("Image type given is not supported");
        }

        // create new image
        $new_image = imagecreatetruecolor($new_size[0], $new_size[1]);
        imagefill($new_image, 0, 0, imagecolorallocate($new_image, 255, 255, 255));

        // This section handles the reproduction of the transparency effect for resized PNGs
        imagealphablending($new_image, true);
        imagesavealpha($new_image, true);
        $transparent_image = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
        imagefilledrectangle($new_image, 0, 0, $new_size[0], $new_size[1], $transparent_image);
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_size[0], $new_size[1], $size[0], $size[1]);

        if (strtolower(substr($target_path, -3)) == "png") {
            imagepng($new_image, $target_path, 9);
        } else if (strtolower(substr($target_path, -3)) == "jpg") {
            imagejpeg($new_image, $target_path, 100);
        } else if (strtolower(substr($target_path, -3)) == "gif") {
            imagegif($new_image, $target_path);
        }
        return $target_path;
    }

}

?>