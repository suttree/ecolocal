<?php
/* $Id: memory.lib.php 8100 2005-12-07 10:01:43Z cybot_tm $ */
// vim: expandtab sw=4 ts=4 sts=4:
/**
 * the MEMORY (HEAP) storage engine
 */
class PMA_StorageEngine_memory extends PMA_StorageEngine
{
    /**
     * returns array with variable names dedicated to MyISAM storage engine
     *
     * @return  array   variable names
     */
    function getVariables()
    {
        return array(
            'max_heap_table_size' => array(
                'type'  => PMA_ENGINE_DETAILS_TYPE_SIZE,
            ),
        );
    }
}

?>
