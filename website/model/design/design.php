<?php
class ModelDesignDesign extends Model {

	public function getDesign($design_data)
	{
		$sql = ("SELECT * FROM " . DB_PREFIX . "setting WHERE code = 'design' AND `key` LIKE '" . $design_data . "%' ORDER BY setting_id DESC ");

		$result = $this->db->query($sql);

		return $result->rows;	
	}

}
