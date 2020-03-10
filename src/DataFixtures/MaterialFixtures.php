<?php

namespace App\DataFixtures;

use App\Entity\MaterialInfo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class MaterialFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $obj = new MaterialInfo();
        $obj->setMaterialCode('0000000000');
        $obj->setMaterialName('DUMMY');
        $obj->setMaterialFullName('DUMMY');
        $manager->persist($obj);
        $manager->flush();
        $this->addReference("DUMMY_MATERIAL_REF", $obj);
    }
}
