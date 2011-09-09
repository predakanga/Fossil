<?php

/*
 * Copyright (c) 2011, predakanga
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the <organization> nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace Fossil\Plugins\Users\Models;

/**
 * @author predakanga
 * @Entity
 * @F:InitialDataset("plugins/users/data/users.yml")
 */
class User extends \Fossil\Models\Model {
    /**
     * @Id @GeneratedValue @Column(type="integer")
     * @var int
     */
    protected $id;
    
    /** @Column() */
    protected $name;
    
    /** @Column() */
    protected $password;
    
    /**
     * @ManyToOne(targetEntity="UserClass", inversedBy="members")
     * @JoinColumn(name="userClass_id", referencedColumnName="id", nullable=false)
     * @var UserClass
     */
    protected $userClass;
    
    /**
     * @ManyToMany(targetEntity="Permission")
     * @JoinTable(name="users_granted_permissions")
     * @var Permission[]
     */
    protected $grantedPermissions;
    /**
     * @ManyToMany(targetEntity="Permission")
     * @JoinTable(name="users_revoked_permissions")
     * @var Permission[]
     */
    protected $revokedPermissions;
    
    protected function hashPassword($value) {
        return md5($value);
    }
    
    protected function setPassword($value) {
        // Hash the password - temporary
        $this->password = $this->hashPassword($value);
    }
    
    protected function verifyPassword($value) {
        return $this->hashPassword($value) == $this->password;
    }
    
    public static function me() {
        // TODO: Add cookie support
        if(isset(OM::Session("FossilAuth")->user)) {
            // Attach the user to the EM if it's not already
            $user = OM::Session("FossilAuth")->user;
            if(!OM::ORM()->getEM()->contains($user))
                OM::ORM()->getEM()->merge($user);
            return $user;
        } else if($haveCookie) {
            
        }
        return null;
    }
    
    public function getRoles() {
        return $this->userClass->roles->toArray();
    }
    
    public function getPermissions() {
        $permissions = array();
        foreach($this->getRoles() as $role) {
            $permissions = array_merge($permissions, $role->permissions->toArray());
        }
        // Add/subtract user-specific permissions
        foreach($this->revokedPermissions as $perm) {
            if($index = array_search($perm, $permissions, true)) {
                unset($permissions[$index]);
            }
        }
        foreach($this->grantedPermissions as $perm) {
            if(!in_array($perm, $permissions))
                $permissions[] = $perm;
        }
        return $permissions;
    }
    
    public function hasRole(Role $role) {
        return in_array($role, $this->getRoles());
    }
    
    public function hasPermission(Permission $permission) {
        return in_array($permission, $this->getPermissions());
    }
}

?>
