<?php
//
// Copyright (c) Xerox Corporation, CodeX Team, 2001-2005. All rights reserved
//
// $Id$
//

require_once('include/DataAccessObject.class.php');

/**
 *  Data Access Object for Permissions 
 */
class PermissionsDao extends DataAccessObject {
    /**
    * Constructs the PermissionsDao
    * @param $da instance of the DataAccess class
    */
    function PermissionsDao( & $da ) {
        DataAccessObject::DataAccessObject($da);
    }
    
    /**
    * Gets all tables of the db
    * @return DataAccessResult
    */
    function & searchAll() {
        $sql = "SELECT * FROM permissions";
        return $this->retrieve($sql);
    }
    
    /**
    * Searches Permissions by PermissionType 
    * @return DataAccessResult
    */
    function & searchByPermissionType($permissionType) {
        $sql = sprintf("SELECT object_id, ugroup_id FROM permissions WHERE permission_type = %s",
				"'".$permissionType."'");
        return $this->retrieve($sql);
    }

    /**
    * Searches Permissions by ObjectId 
    * @return DataAccessResult
    */
    function & searchByObjectId($objectId) {
        $sql = sprintf("SELECT permission_type, ugroup_id FROM permissions WHERE object_id = %s",
				"'".$objectId."'");
        return $this->retrieve($sql);
    }

    /**
    * Searches Permissions by UgroupId 
    * @return DataAccessResult
    */
    function & searchByUgroupId($ugroupId) {
        $sql = sprintf("SELECT permission_type, object_id FROM permissions WHERE ugroup_id = %s",
				"'".$ugroupId."'");
        return $this->retrieve($sql);
    }

    /**
    * Searches Permissions by ObjectId and Ugroups
    * @return DataAccessResult
    */
    function & searchPermissionsByObjectId($objectId, $ptype=null) {
        if(is_array($objectId)) {
            $_where_clause = ' object_id IN ('.implode(',',$objectId).')';
        }
        else {
            $_where_clause = ' object_id = '.$objectId;
        }
        if($ptype !== null) {
            $_where_clause .= ' AND permission_type IN ('.implode(',',$ptype).')';
        }

        $sql = sprintf("SELECT * FROM permissions WHERE ".$_where_clause);
        return $this->retrieve($sql);
    }

    /**
    * Searches Permissions by TrackerId and Ugroups
    * @return DataAccessResult
    */
    function & searchPermissionsByArtifactFieldId($objectId) {
        $sql = sprintf("SELECT * FROM permissions WHERE object_id LIKE '%s#%%'" ,
				$objectId);
        return $this->retrieve($sql);
    }

    function clonePermissions($source, $target, $perms, $toGroupId=0) {
        foreach($perms as $key => $value) {
            $perms[$key] = $this->da->quoteSmart($value);
        }
        $sql = sprintf('DELETE FROM permissions '.
                        ' WHERE object_id = %s '.
                        '   AND permission_type IN (%s) ',
                        $this->da->quoteSmart($target),
                        implode(', ', $perms)
        );
        $this->update($sql);
        $sql = sprintf('INSERT INTO permissions (object_id, permission_type, ugroup_id) '.
                        ' SELECT %s, permission_type, IFNULL(dst_ugroup_id, permissions.ugroup_id) AS ugid '.
                        ' FROM permissions LEFT JOIN ugroup_mapping ON (to_group_id=%d  and src_ugroup_id = permissions.ugroup_id)'.
                        ' WHERE object_id = %s '.
                        '   AND permission_type IN (%s) ',
                        $this->da->quoteSmart($target),
                        $toGroupId,
                        $this->da->quoteSmart($source),
                        implode(', ', $perms)
        );
        return $this->update($sql);
    }

}


?>