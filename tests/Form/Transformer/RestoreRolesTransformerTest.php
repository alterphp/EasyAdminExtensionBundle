<?php

/**
 * Test for RestoreRolesTransformer.
 *
 * Copied from SonataUserBundle RestoreRolesTransformer.
 *
 * @see https://github.com/sonata-project/SonataUserBundle/blob/master/tests/Form/Transformer/RestoreRolesTransformerTest.php
 */

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Form\Transformer;

use AlterPHP\EasyAdminExtensionBundle\Form\Transformer\RestoreRolesTransformer;
use AlterPHP\EasyAdminExtensionBundle\Helper\EditableRolesHelper;
use PHPUnit\Framework\TestCase;

class RestoreRolesTransformerTest extends TestCase
{
    public function testInvalidStateTransform()
    {
        $this->expectException(\RuntimeException::class);

        $roleBuilder = $this->createMock(EditableRolesHelper::class);

        $transformer = new RestoreRolesTransformer($roleBuilder);
        $transformer->transform(array());
    }

    public function testInvalidStateReverseTransform()
    {
        $this->expectException(\RuntimeException::class);

        $roleBuilder = $this->createMock(EditableRolesHelper::class);

        $transformer = new RestoreRolesTransformer($roleBuilder);
        $transformer->reverseTransform(array());
    }

    public function testNullIsTransformedToNull()
    {
        $roleBuilder = $this->createMock(EditableRolesHelper::class);

        $transformer = new RestoreRolesTransformer($roleBuilder);
        $transformer->setOriginalRoles(array());

        $this->assertNull($transformer->transform(null));
    }

    public function testValidTransform()
    {
        $roleBuilder = $this->createMock(EditableRolesHelper::class);

        $transformer = new RestoreRolesTransformer($roleBuilder);
        $transformer->setOriginalRoles(array());

        $data = array('ROLE_FOO');

        $this->assertSame($data, $transformer->transform($data));
    }

    public function testValidReverseTransform()
    {
        $roleBuilder = $this->createMock(EditableRolesHelper::class);

        $roleBuilder->expects($this->once())->method('getRoles')->will($this->returnValue(array()));

        $transformer = new RestoreRolesTransformer($roleBuilder);
        $transformer->setOriginalRoles(array('ROLE_HIDDEN'));

        $data = array('ROLE_FOO');

        $this->assertSame(array('ROLE_FOO', 'ROLE_HIDDEN'), $transformer->reverseTransform($data));
    }

    public function testTransformAllowEmptyOriginalRoles()
    {
        $roleBuilder = $this->createMock(EditableRolesHelper::class);

        $transformer = new RestoreRolesTransformer($roleBuilder);
        $transformer->setOriginalRoles(null);

        $data = array('ROLE_FOO');

        $this->assertSame($data, $transformer->transform($data));
    }

    public function testReverseTransformAllowEmptyOriginalRoles()
    {
        $roleBuilder = $this->createMock(EditableRolesHelper::class);

        $roleBuilder->expects($this->once())->method('getRoles')->will($this->returnValue(array()));

        $transformer = new RestoreRolesTransformer($roleBuilder);
        $transformer->setOriginalRoles(null);

        $data = array('ROLE_FOO');

        $this->assertSame(array('ROLE_FOO'), $transformer->reverseTransform($data));
    }

    public function testReverseTransformRevokedHierarchicalRole()
    {
        $roleBuilder = $this->createMock(EditableRolesHelper::class);

        $availableRoles = array(
            'ROLE_SONATA_ADMIN' => 'ROLE_SONATA_ADMIN',
            'ROLE_COMPANY_PERSONAL_MODERATOR' => 'ROLE_COMPANY_PERSONAL_MODERATOR: ROLE_COMPANY_USER',
            'ROLE_COMPANY_NEWS_MODERATOR' => 'ROLE_COMPANY_NEWS_MODERATOR: ROLE_COMPANY_USER',
            'ROLE_COMPANY_BOOKKEEPER' => 'ROLE_COMPANY_BOOKKEEPER: ROLE_COMPANY_USER',
            'ROLE_USER' => 'ROLE_USER',
        );
        $roleBuilder->expects($this->once())->method('getRoles')->will($this->returnValue($availableRoles));

        // user roles
        $userRoles = array('ROLE_COMPANY_PERSONAL_MODERATOR', 'ROLE_COMPANY_NEWS_MODERATOR', 'ROLE_COMPANY_BOOKKEEPER');
        $transformer = new RestoreRolesTransformer($roleBuilder);
        $transformer->setOriginalRoles($userRoles);

        // now we want to revoke role ROLE_COMPANY_PERSONAL_MODERATOR
        $revokedRole = array_shift($userRoles);
        $processedRoles = $transformer->reverseTransform($userRoles);

        $this->assertNotContains($revokedRole, $processedRoles);
    }

    public function testReverseTransformHiddenRole()
    {
        $roleBuilder = $this->createMock(EditableRolesHelper::class);

        $availableRoles = array(
            'ROLE_SONATA_ADMIN' => 'ROLE_SONATA_ADMIN',
            'ROLE_ADMIN' => 'ROLE_ADMIN: ROLE_USER ROLE_COMPANY_ADMIN',
        );
        $roleBuilder->expects($this->once())->method('getRoles')->will($this->returnValue($availableRoles));

        // user roles
        $userRoles = array('ROLE_USER', 'ROLE_SUPER_ADMIN');
        $transformer = new RestoreRolesTransformer($roleBuilder);
        $transformer->setOriginalRoles($userRoles);

        // add a new role
        array_push($userRoles, 'ROLE_SONATA_ADMIN');
        // remove existing user role that is not availableRoles
        unset($userRoles[array_search('ROLE_SUPER_ADMIN', $userRoles)]);
        $processedRoles = $transformer->reverseTransform($userRoles);

        $this->assertContains('ROLE_SUPER_ADMIN', $processedRoles);
    }
}
