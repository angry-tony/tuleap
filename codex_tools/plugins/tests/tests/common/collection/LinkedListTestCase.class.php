<?php
require_once('CollectionTestCase.class.php');

/**
 * Copyright (c) Xerox Corporation, CodeX Team, 2001-2005. All rights reserved
 * 
 * $Id: CollectionTest.php,v 1.1 2005/05/10 09:48:10 nterray Exp $
 *
 */
class LinkedListTestCase extends CollectionTestCase {
    
    function LinkedListTestCase($name = 'Collection test', $collection_class_name = 'you_must_define_classname') {
        $this->CollectionTestCase($name, $collection_class_name);
    }
    
    function testOrder() {
        $a = 'a';
        $b = 'b';
        $c = 'c';
        $l =& new $this->collection_class_name();
        $l->add($c);
        $l->add($b);
        $l->add($a);
        $it =& $l->iterator();
        $element =& $it->current();
        $this->assertReference($element, $c);
        $it->next();
        $element =& $it->current();
        $this->assertReference($element, $b);
        $it->next();
        $element =& $it->current();
        $this->assertReference($element, $a);
    }
    
    function testEqualsDifferentOrder() {
        $a = 'a';
        $b = 'b';
        $l1 =& new $this->collection_class_name();
        $l1->add($a);
        $l1->add($b);
        $l2 =& new $this->collection_class_name();
        $l2->add($b);
        $l2->add($a);
        $this->assertFalse($l1->equals($l2));
    }

}
//We just tells SimpleTest to always ignore this testcase
SimpleTestOptions::ignore('LinkedListTestCase');

?>
