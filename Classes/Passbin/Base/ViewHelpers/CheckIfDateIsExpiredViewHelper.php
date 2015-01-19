<?php
namespace Passbin\Base\ViewHelpers;


use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

class CheckIfDateIsExpiredViewHelper extends AbstractViewHelper
{
	/**
	 * @param \DateTime $date
	 * @return string
	 */
	public function render($date)
	{
		if($date->format("Y-m-d H:i:s") < date("Y-m-d H:i:s")) {
			return 1;
		} else {
			return 0;
		}
	}
}