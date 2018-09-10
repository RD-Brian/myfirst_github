<?php
class ModelToolImage extends Model {
	public function resize($filename, $width, $height) {
		if (!is_file(DIR_UPLOADS . $filename) || substr(str_replace('\\', '/', realpath(DIR_UPLOADS . $filename)), 0, strlen(DIR_UPLOADS)) != str_replace('\\', '/', DIR_UPLOADS)) {
			return;
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		$image_old = $filename;
		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

		if (!is_file(DIR_UPLOADS . $image_new) || (filemtime(DIR_UPLOADS . $image_old) > filemtime(DIR_UPLOADS . $image_new))) {
			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_UPLOADS . $image_old);
				 
			if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) { 
				return DIR_UPLOADS . $image_old;
			}
						
			$path = '';

			$directories = explode('/', dirname($image_new));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir(DIR_UPLOADS . $path)) {
					@mkdir(DIR_UPLOADS . $path, 0777);
				}
			}

			if ($width_orig != $width || $height_orig != $height) {
				$image = new Image(DIR_UPLOADS . $image_old);
				$image->resize($width, $height);
				$image->save(DIR_UPLOADS . $image_new,100);
			} else {
				copy(DIR_UPLOADS . $image_old, DIR_UPLOADS . $image_new);
			}
		}
		
		$image_new = str_replace(' ', '%20', $image_new);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +
		
		return HTTP_UPLOADS . $image_new;
	}
}
