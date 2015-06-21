<?php
use Sabre\DAV;
use Sabre\CardDAV;
use Toyota\Component\Ldap;

class SukatAddressBook extends DAV\Collection implements CardDAV\IAddressBook, DAV\IMultiGet
{
    private static $ATTRS = [
        'uid', 'cn', 'givenName', 'sn', 'telephoneNumber'
    ];
    /** @var Ldap\Core\Manager */
    private $ldap;
    /** @var SukatCard[]|null */
    private $children;
    
    public function __construct($base_dn)
    {
        $this->ldap = new Ldap\Core\Manager(
            [
                'hostname' => 'ldap.su.se',
                'base_dn'  => $base_dn,
                'security' => 'SSL'
            ],
            new Ldap\Platform\Native\Driver
        );
        $this->ldap->connect();
        $this->ldap->bind();
    }

    public function getName()
    {
        return 'sukat';
    }

    public function getChildren()
    {
        if (!isset($this->children)) {
            $entries = $this->ldap->search(
                null,
                '(telephoneNumber=*)',
                true,
                self::$ATTRS
            );

            $this->children = [];
            foreach ($entries as $entry) {
                $this->children[$entry->get('uid')[0] . '.vcf']
                        = new SukatCard($entry);
            }
        }
        return $this->children;
    }

    public function getMultipleChildren(array $paths)
    {
        $children = $this->getChildren();
        $ret = [];
        foreach ($paths as $path) {
            if (isset($children[$path])) {
                $ret[] = $children[$path];
            }
        }
        return $ret;
    }
    
    public function getChild($name)
    {
        static $calledBefore = false;

        // If function is called multiple times we might as well
        // fetch all entries to speed up
        if ($calledBefore) {
            $children = $this->getChildren();
            if (isset($children[$name])) {
                return $children[$name];
            } else {
                throw new DAV\Exception\NotFound('Not Found');
            }
        }

        $calledBefore = true;

        if (!preg_match('/^([.a-z0-9-]+)\.vcf$/', $name, $matches)) {
            throw new DAV\Exception\NotFound('Not Found');
        }

        $entries = $this->ldap->search(
            null,
            "(uid=$matches[1])",
            true,
            self::$ATTRS
        );

        if ($entries->current()) {
            return new SukatCard($entries->current());
        } else {
            throw new DAV\Exception\NotFound('Not Found');
        }
    }
}
