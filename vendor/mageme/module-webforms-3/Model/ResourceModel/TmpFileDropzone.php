<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebForms\Model\ResourceModel;


use MageMe\WebForms\Api\Data\TmpFileDropzoneInterface;
use MageMe\WebForms\Setup\Table\TmpFileDropzoneTable;

class TmpFileDropzone extends AbstractFile
{
    const DB_TABLE = TmpFileDropzoneTable::TABLE_NAME;
    const ID_FIELD = TmpFileDropzoneInterface::ID;
    const DELETE_EVENT_NAME = 'webforms_dropzone_delete';
}
