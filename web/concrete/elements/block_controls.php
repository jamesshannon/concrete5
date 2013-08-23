<?php  
	defined('C5_EXECUTE') or die("Access Denied.");
	if ($a->isGlobalArea()) {
		$c = Page::getCurrentPage();
		$cID = $c->getCollectionID();
	} else {
		$cID = $b->getBlockCollectionID();
		$c = $b->getBlockCollectionObject();
	}
	$btw = BlockType::getByID($b->getBlockTypeID());
	$btOriginal = $btw;
	$bID = $b->getBlockID();
	$heightPlus = 20;
	if ($btw->getBlockTypeHandle() == BLOCK_HANDLE_SCRAPBOOK_PROXY) {
		$_bi = $b->getInstance();
		$_bo = Block::getByID($_bi->getOriginalBlockID());
		$btOriginal = BlockType::getByHandle($_bo->getBlockTypeHandle());
		$heightPlus = 80;
	}
	$isAlias = $b->isAlias();
	$u = new User();
	$numChildren = (!$isAlias) ? $b->getNumChildren() : 0;
	if ($isAlias) {
		//$message = 'This item is an alias. Editing it will create a new instance of this block.';
		$deleteMessage = t('Do you want to delete this block?');
	} else if ($numChildren) {
		$editMessage =  t('This block is aliased by other blocks. If you edit this block, your changes will effect those other blocks. Are you sure you want to edit this block?');
		$deleteMessage = t('Do you want to delete this block? This item is an original. If you delete it, you will delete all blocks aliased to it');
	} else {
		$deleteMessage = t('Do you want to delete this block?');
	}

$id = $bID . $a->getAreaID();

$menuObj = array(
	'id' => $id,
	'type' => 'BLOCK',
	'arHandle' => $a->getAreaHandle(),
	'aID' => $a->getAreaID(),
	'bID' => $bID,
	'cID' => $cID,
	'canCopyToScrapbook' => true,
	'canModifyGroups' => $p->canEditBlockPermissions() && PERMISSIONS_MODEL != 'simple' && ! $a->isGlobalArea(),
	'canScheduleGuestAccess' => PERMISSIONS_MODEL != 'simple' && $p->canGuestsViewThisBlock() && $p->canScheduleGuestAccess() && ! $a->isGlobalArea(),
	'canDesign' => $p->canEditBlockDesign() && ENABLE_CUSTOM_DESIGN,
	'canEditBlockCustomTemplate' => (bool) $p->canEditBlockCustomTemplate(),
	'canAdmin' => (bool) $p->canEditBlockPermissions(),
	'canDelete' => (bool) $p->canDeleteBlock(),
	'deleteMessage' => $deleteMessage,
	'canAliasBlockOut' => $c->isMasterCollection() && ! $a->isGlobalArea(),
	'canSetupComposer' => (bool) CollectionType::getByID($c->getCollectionTypeID())->isCollectionTypeIncludedInComposer(),
	'canArrange' => $p->canWrite() && ! $a->isGlobalArea(),
	'editMessage' => $editMessage
);

if ($p->canWrite() && $btOriginal->getBlockTypeHandle() != BLOCK_HANDLE_STACK_PROXY) {
	$menuObj = $menuObj + array(
		'canWrite' => true,
		'hasEditDialog' => $b->isEditable(),
		'btName' => t($btOriginal->getBlockTypeName()),
		'width' => $btOriginal->getBlockTypeInterfaceWidth(),
		'height' => $btOriginal->getBlockTypeInterfaceHeight() + $heightPlus
	);
} else if ($btOriginal->getBlockTypeHandle() == BLOCK_HANDLE_STACK_PROXY) {
	$bi = (is_object($_bo)) ? $bi = $_bo->getInstance() : $b->getInstance();
	$stack = Stack::getByID($bi->stID);
	
	if (is_object($stack)) {
		$sp = new Permissions($stack);
		if ($sp->canWrite()) {
			$menuObj = $menuObj + array(
				'canWriteStack' => true,
				'stID' => $bi->stID
			);
		}
	}
}

?>

<script type="text/javascript">
$(function() {
	ccm_menuInit(<?php echo json_encode($menuObj) ?>);
});
</script>
