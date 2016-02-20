<?php

class UserTest extends PHPUnit_Framework_TestCase
{
    public function testValidUserName()
    {
        $user = new User();
        $user->login = "éé'àç_'";
        $errors = $user->validateController();
        $this->assertEquals(1, sizeof($errors));

        $user->login = "jessy";
        $errors = $user->validateController();
        $this->assertEquals(1, sizeof($errors));

        $user->login = "jessy.lenne@live.fr";
        $errors = $user->validateController();
        $this->assertEquals(0, sizeof($errors));
    }
}