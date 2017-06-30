<?php

namespace ZeusTest\Post;

use Zeus\Annotations\Route;

class PostController
{

    /** @Route("post") */
    public static function manage()
    {
        echo 'Managing posts';
    }

    /** @Route("post/new") */
    public static function newPost()
    {
        echo 'New post';
    }

    /** @Route("post/edit/$id") */
    public static function editPost($id)
    {
        echo 'Editing post ' . $id;
    }

    /** @Route("post/edit/$name/$id") */
    public static function editFullPost($id, $name)
    {
        echo 'Editing full post ' . $id . ' with name ' . $name;
    }

    /** @Route("post/show/$id") */
    public static function showPost($id)
    {
        echo 'Showing post ' . $id;
    }

}
