<?php
class ControllerProblemProblem extends Controller {
	private $error = array();

	/**
	 * 顯示列表
	 * @return [type] [description]
	 */
	public function index() {
		//doc
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));
		//load
		$this->load->language('problem/problem');
		$this->load->model('problem/problem');
		//靜態Data

		//動態data
		if (isset($this->request->get['problem_category_id'])) {
			$problem_category_id = $this->request->get['problem_category_id'];
		} else {
			$problem_category_id = '';
		}
		//過濾資料
		$filter_data = array(
			'problem_category_id' => $problem_category_id,
		);

		//分類
		$problems_category = $this->model_problem_problem->getCategories($filter_data);

		foreach ($problems_category as $key => $value) {
			$data['problems_category'][] = array(
				'name'    => $value['name'],
				'href'		=> $this->url->link('problem/problem').'&problem_category_id='.$value['problem_category_id'],
			);
		}

		//問題
		$problems = $this->model_problem_problem->getProblems($filter_data);

		foreach ($problems as $key => $value) {
			$data['problems'][] = array(
				'name'    			=> $value['name'],
				'description'		=> html_entity_decode($value['description'], ENT_QUOTES, 'UTF-8'),
			);
		}


		$this->_output('problem/problem',$data);
	}
}
