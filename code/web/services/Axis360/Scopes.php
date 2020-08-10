<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/Axis360/Axis360Scope.php';

class Axis360_Scopes extends ObjectEditor
{
	function getObjectType(){
		return 'Axis360Scope';
	}
	function getToolName(){
		return 'Scopes';
	}
	function getModule(){
		return 'Axis360';
	}
	function getPageTitle(){
		return 'Axis 360 Scopes';
	}
	function getAllObjects(){
		$object = new Axis360Scope();
		$object->orderBy('name');
		$object->find();
		$objectList = array();
		while ($object->fetch()){
			$objectList[$object->id] = clone $object;
		}
		return $objectList;
	}
	function getObjectStructure(){
		return Axis360Scope::getObjectStructure();
	}
	function getPrimaryKeyColumn(){
		return 'id';
	}
	function getIdKeyColumn(){
		return 'id';
	}
	function getAllowableRoles(){
		return array('opacAdmin', 'libraryAdmin', 'cataloging', 'superCataloger');
	}
	function canAddNew(){
		return UserAccount::userHasRole('opacAdmin') || UserAccount::userHasRole('libraryAdmin') || UserAccount::userHasRole('cataloging') || UserAccount::userHasRole('superCataloger');
	}
	function canDelete(){
		return UserAccount::userHasRole('opacAdmin') || UserAccount::userHasRole('libraryAdmin') || UserAccount::userHasRole('cataloging') || UserAccount::userHasRole('superCataloger');
	}
	function getAdditionalObjectActions($existingObject){
		return [];
	}

	function getInstructions(){
		return '';
	}
}