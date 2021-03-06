<?php

namespace Vilemka\Components;

use Nette\Utils\Strings;

class PhotoSlider extends \Nette\Application\UI\Control
{

	/** @persistent */
	public $photoMove = 0;

	/** @var int */
	protected $displayedPhotosCount = 3;


	public function render()
	{
		$this->template->setFile(__DIR__ . '/photoSlider.latte');
		$this->loadPhotoList();
		$this->template->render();
	}


	private function loadPhotoList()
	{
		$photosIterator = \Nette\Utils\Finder::findFiles('*.jpg')->in(__DIR__ . '/../../public/images/photos');
		$photosIterator = iterator_to_array($photosIterator);
		$photosCount = count($photosIterator);
		if ($this->photoMove < 0 || $this->photoMove > ($photosCount -1)) {
			$this->photoMove = 0;
		}
		$this->template->photoMove = $this->photoMove;

		$previous = $this->photoMove -1;
		$next = $this->photoMove +1;
		if ($previous == -1) {
			$previous = $photosCount -1;
		}
		if ($next == $photosCount) {
			$next = 0;
		}
		$this->template->previous = $previous;
		$this->template->next = $next;

		$i = 0;
		$photos = $show = [];
		$showCount = 0;
		foreach ($photosIterator as $photo) {
			$filename = $photo->getFilename();
			$alt = Strings::firstUpper(str_replace('_', ' ', substr($filename, 0, strrpos($filename, '.'))));
			$photos[$alt] = $filename;
			if ($i == $this->photoMove) {
				$showCount = $this->displayedPhotosCount;
			}
			if ($showCount) {
				$show[$filename] = TRUE;
				--$showCount;
			}
			++$i;
		}

		if ($showCount) {
			reset($photos);
			while ($showCount--) {
				$movePhoto = current($photos);
				unset($photos[key($photos)]);
				$photos[] = $movePhoto;
				$show[$movePhoto] = TRUE;
			}
			$photos = array_values($photos);
		}

		$this->template->photos = $photos;
		$this->template->show = $show;
	}

}
