<?php


class Coolrunner_Parcel_Size{
	public static function getSizes(){
		return get_option('coolrunner_parcel_sizes', []);
	}

	public static function setSizes($sizes = []){
		return update_option('coolrunner_parcel_sizes', $sizes);
	}

	public static function getSizeId($id)
	{
		$sizes = self::getSizes();

		return $sizes['size_'.$id];
	}

	public static function getNextSizeId()
	{
		$sizes = self::getSizes();

		if (empty($sizes)) {
			return 0;
		}

		return count($sizes);
	}

	public static function saveSize($size)
	{
		$sizes = self::getSizes();
		self::setSizes();

		if (empty($sizes)){
			$isPrimary = true;
		} else {
			$isPrimary = ($sizes['size_'.$size['id']]['is_primary'] == true) ? true : false;
		}

		$sizes['size_'.$size['id']] = [
			'id'					=> $size['id'],
			'name'					=> $size['name'],
			'length'				=> $size['length'],
			'width'					=> $size['width'],
			'height'				=> $size['height'],
			'is_primary'			=> $isPrimary
		];

		self::setSizes($sizes);

		if ($isPrimary) {
			self::changePrimarySize($size['id']);
		}

		return $sizes;
	}

	public static function deleteSize($id)
	{
		$sizes = self::getSizes();

		if (empty($sizes)) {
			return false;
		}

		if (!array_key_exists('size_'.$id, $sizes)) {
			return false;
		}

		$wasPrimary = $sizes['size_'.$id]['is_primary'];

		unset($sizes['size_'.$id]);

		//Prevents deleting the primary size while there are more than 1 size
		if ($wasPrimary == true && count($sizes) >= 1) {
			reset($sizes);
			$key = key($sizes);

			echo $key;
			$sizes[$key]['is_primary'] = true;
		}

		if ( !self::setSizes($sizes) ) {
			return false;
		}

		return true;
	}

	public static function changePrimarySize($id)
	{
		$sizes = self::getSizes();

		if (empty($sizes)) {
			return false;
		}

		if (!array_key_exists('size_'.$id, $sizes)) {
			return false;
		}


		foreach ($sizes as $key => $size) {
			if ($size['id'] == $id) {
				$sizes['size_'.$size['id']]['is_primary'] = true;
			} else {
				$sizes['size_'.$size['id']]['is_primary'] = false;
			}
		}

		self::setSizes($sizes);

		return $sizes;
	}
}