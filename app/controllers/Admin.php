<?php

namespace app\controllers;

use app\core\Controller;
use app\lib\Pagination;
use app\models\Main;

class Admin extends Controller
{

	public function __construct($route)
    {
		parent::__construct($route);
		$this->view->layout = 'admin';
	}

	public function loginAction()
    {
		if (isset($_SESSION['admin']))
		{
			$this->view->redirect('blog/admin/add');
		}
		if (!empty($_POST))
		{
			if (!$this->model->loginValidate($_POST))
			{
				$this->view->message('error', $this->model->error);
			}
			$_SESSION['admin'] = true;
			$this->view->location('blog/admin/add');
		}
		$this->view->render('Login');
	}

	public function addAction()
    {
		if (!empty($_POST))
		{
			if (!$this->model->postValidate($_POST, 'add'))
			{
				$this->view->message('error', $this->model->error);
			}
			$id = $this->model->postAdd($_POST);
			if (!$id)
			{
				$this->view->message('success', 'Request processing error');
			}
			$this->model->postUploadImage($_FILES['img']['tmp_name'], $id);
			$this->view->message('success', 'Post Add');
		}
		$this->view->render('Post Add');
	}

	public function editAction()
    {
		if (!$this->model->isPostExists($this->route['id']))
		{
			$this->view->errorCode(404);
		}
		if (!empty($_POST))
		{
			if (!$this->model->postValidate($_POST, 'edit'))
			{
				$this->view->message('error', $this->model->error);
			}
			$this->model->postEdit($_POST, $this->route['id']);
			if ($_FILES['img']['tmp_name'])
			{
				$this->model->postUploadImage($_FILES['img']['tmp_name'], $this->route['id']);
			}
			$this->view->message('success', 'Saved');
		}
		$vars = [
			'data' => $this->model->postData($this->route['id'])[0],
		];
		$this->view->render('Post Edit', $vars);
	}

	public function deleteAction()
    {
		if (!$this->model->isPostExists($this->route['id']))
		{
			$this->view->errorCode(404);
		}
		$this->model->postDelete($this->route['id']);
		$this->view->redirect('blog/admin/posts');
	}

	public function logoutAction()
    {
		unset($_SESSION['admin']);
		$this->view->redirect('blog/admin/login');
	}

	public function postsAction()
    {
		$mainModel = new Main;
		$pagination = new Pagination($this->route, $mainModel->postsCount());
		$vars = [
			'pagination' => $pagination->get(),
			'list' => $mainModel->postsList($this->route),
		];
		$this->view->render('Posts', $vars);
	}
}