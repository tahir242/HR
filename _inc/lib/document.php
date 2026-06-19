<?php

class Document 
{
	private $registry;
	private $bodyClass = array();
	private $title;
	private $description;
	private $keywords;
	private $links = array();
	private $styles = array();
	private $scripts = array();

	public function __construct($registry)
	{
		$this->registry = $registry;
	}

	public function setBodyClass($name=false,$force=false)
	{
		$user = $this->registry->get('user');

		if (isset($this->bodyClass[$name]) && $force) {
			unset($this->bodyClass[$name]);
		}

		if (!isset($this->bodyClass[$name])) {
			$this->bodyClass[$name] = $name;
		}

		return $this->bodyClass;
	}

	public function getBodyClass()
	{
		$class_name = '';

		if (!empty($this->bodyClass)) {
			foreach ($this->bodyClass as $class) {
				$class_name .= ' ' . $class;
			}
		}

		return $class_name;
	}

	public function setTitle($title) 
	{
		$this->title = $title;
	}

	public function getTitle() 
	{
		return $this->title;
	}

	public function setDescription($description) 
	{
		$this->description = $description;
	}

	public function getDescription() 
	{
		return $this->description;
	}

	public function setKeywords($keywords) 
	{
		$this->keywords = $keywords;
	}

	public function getKeywords() 
	{
		return $this->keywords;
	}

	public function addLink($href, $rel) 
	{
		$this->links[$href] = array(
			'href' => $href,
			'rel'  => $rel
		);
	}

	public function getLinks() 
	{
		return $this->links;
	}

	public function addStyle($href, $rel = 'stylesheet', $media = 'screen') 
	{
		$this->styles[$href] = array(
			'href'  => $href,
			'rel'   => $rel,
			'media' => $media
		);
	}

	public function getStyles() 
	{
		return $this->styles;
	}

	public function addScript($script) 
	{
		$this->scripts[md5($script)] = $script;
	}

	public function getScripts() 
	{
		return $this->scripts;
	}
}