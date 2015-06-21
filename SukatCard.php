<?php
use Sabre\DAV;
use Sabre\CardDAV;
use Sabre\VObject\Component\VCard;

class SukatCard extends DAV\File implements CardDAV\ICard
{
    private $name;
    private $data;

    function __construct($entry)
    {
        $this->name = $entry->get('uid')[0] . '.vcf';

        $tel = $entry->get('telephonenumber')[0];
        $shortTel = substr($tel, -4);

        $vcard = new VCard(
            [
                'FN'  => $entry->get('cn')[0],
                'N'   => [ $entry->get('sn')[0],
                           $entry->get('givenname')[0] ],
                'TEL' => $shortTel,
            ]
        );

        $this->data = $vcard->serialize();
    }

    function getName()
    {
        return $this->name;
    }

    function get()
    {
        return $this->data;
    }

    function getContentType()
    {
        return 'text/vcard; charset=utf-8';
    }

    function getSize()
    {
        return strlen($this->data);
    }
}
