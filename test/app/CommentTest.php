<?php

class CommentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    private $user;

    protected function setUp()
    {
        $this->user = new User();
        $this->user->login = "jessy.lenne@stadeline.fr";
        $this->user->password = Tools::encrypt("secret");
        $this->user->save();
    }

    protected function tearDown()
    {
        $this->user->delete();
    }

    public function testAddComment()
    {
        $comment = new Comment();
        $comment->id_user = $this->user->id;
        $errors = $comment->validateController();

        $this->assertEquals(3, sizeof($errors)); // username, repository, comment required

        $nbComments = sizeof($comments = $this->user->comments());
        $this->assertEquals(0, $nbComments);
        $comment->setUsername("jessylenne");
        $comment->setRepository("jessylenne");
        $this->setExpectedExceptionRegExp('Exception', '/Veuillez fournir un commentaire valide/');
        $comment->setComment('<script type="text/javascript">console.log("Yeah Fail!")</script>');
        $this->assertFalse($comment->save());
        $comment->setComment("comment");
        $this->assertTrue($comment->save());
        $this->assertEquals($nbComments + 1, sizeof($this->user->comments()));

        $comment->delete();
    }
}