<?php

namespace App\Controllers;

use App\System\HTTP\Request;
use App\System\HTTP\Response;

class UserController
{
    public function index(Request $request, array $params): Response
    {
        // 获取所有用户
        return new Response('List of users');
    }

    public function show(Request $request, array $params): Response
    {
        $id = $params['id'];
        // 获取 ID 为 $id 的用户
        return new Response("User with ID: $id");
    }

    public function store(Request $request, array $params): Response
    {
        // 创建新用户
        return new Response('Creating new user');
    }

     public function update(Request $request, array $params): Response
    {
        $id = $params['id'];
        // 更新 ID 为 $id 的用户
        return new Response("Updating user with ID: $id");
    }

    public function destroy(Request $request, array $params): Response
    {
        $id = $params['id'];
        // 删除 ID 为 $id 的用户
        return new Response("Deleting user with ID: $id");
    }
}
