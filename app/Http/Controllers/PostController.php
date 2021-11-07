<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
  /**
   * Function to handler Create Post by User
   */
  function store(Request $request)
  {
    // GET USER BY API TOKEN
    $authorization = $request->header('Authorization');
    $apiToken = explode(' ', $authorization);
    $user = User::where('api_token', $apiToken[1])->first();

    /**
     * GET REQUEST PAYLOAD
     * @title, @content, @status, @thumbnail
     */
    $title = $request->input("title");
    $content = $request->input("content");
    $status = $request->input("status");
    $thumbnail = $request->file("thumbnail");

    // VALIDATING REQUEST PAYLOAD
    if (!$title) {
      $model_response = [
        "status" => false,
        "message" => "Blog title is required!",
        "data" => null,
      ];
      return response()->json($model_response, 400);
    }

    if (!$content) {
      $model_response = [
        "status" => false,
        "message" => "Blog content is required!",
        "data" => null,
      ];
      return response()->json($model_response, 400);
    }

    if (!$status) {
      $model_response = [
        "status" => false,
        "message" => "Blog status is required!",
        "data" => null,
      ];
      return response()->json($model_response, 400);
    }

    if (!$request->hasFile("thumbnail")) {
      $model_response = [
        "status" => false,
        "message" => "Blog thumbnail is required!",
        "data" => $thumbnail,
      ];
      return response()->json($model_response, 400);
    }

    if (!is_image($thumbnail)) {
      $model_response = [
        "status" => false,
        "message" => "Blog thumbnail must be jpg/png format!",
        "data" => null,
      ];
      return response()->json($model_response, 400);
    }
    // END VALIDATING REQUEST PAYLOAD

    /**
     * Creating slug from title
     * Insert post to DB Server
     * Uploading thumbnail to /post/image/ folder.
     */
    $slug = slugify($title);
    $path_thumbnail = './post/image/';
    $name_thumbnail = 'ut-' . $user->id . time() . '.' . $thumbnail->getClientOriginalExtension();

    $post = Post::create([
      'title' => $title,
      'slug' => $slug,
      'thumbnail' => $name_thumbnail,
      'content' => $content,
      'status' => $status,
      'user_id' => $user->id,
    ]);

    if ($thumbnail->move($path_thumbnail, $name_thumbnail)) {
      $post->thumbnail = $this->baseImage() . $name_thumbnail;
    }

    return response()->json([
      "status" => true,
      "message" => "Create User Post Success!",
      "data" => $post,
    ], 201);
  }

  /**
   * Function to handler Get List User Post
   */
  function index(Request $request)
  {
    // GET USER BY API TOKEN
    $authorization = $request->header('Authorization');
    $apiToken = explode(' ', $authorization);
    $user = User::where('api_token', $apiToken[1])->first();

    /**
     * Get Post List by user_id
     * Limit only 10 items
     * Replacing thumbnail with full path
     */
    $post = Post::with('author')->where('user_id', $user->id)->take(10)->get();
    foreach ($post as $postItem) {
      $postItem->thumbnail = $this->baseImage() . $postItem->thumbnail;
    }

    return response()->json([
      "status" => true,
      "message" => "Get List User Post Success!",
      "data" => $post,
    ], 200);
  }

  /**
   * Function to handler Get User Post by ID
   */
  function show(Request $request, $postId)
  {
    // GET USER BY API TOKEN
    $authorization = $request->header('Authorization');
    $apiToken = explode(' ', $authorization);
    $user = User::where('api_token', $apiToken[1])->first();

    // Validating Post ID
    if (!$postId) {
      $model_response = [
        "status" => false,
        "message" => "Post id is required!",
        "data" => null,
      ];
      return response()->json($model_response, 400);
    }

    /**
     * Get Data Post by Id & user_id
     * Check if exists, replace thumbnail with full path
     * if doesn't exist, return the error message
     */
    $post = Post::with('author')->where('id', $postId)->where('user_id', $user->id)->first();
    if ($post) {
      $post->thumbnail = $this->baseImage() . $post->thumbnail;

      return response()->json([
        "status" => true,
        "message" => "Success Get Detail Post!",
        "data" => $post,
      ], 200);
    } else {
      return response()->json([
        "status" => false,
        "message" => "Post not found!",
        "data" => null,
      ], 404);
    }
  }

  /**
   * Function to handler Update User Post by ID
   */
  function update(Request $request, $postId)
  {
    // GET USER BY API TOKEN
    $authorization = $request->header('Authorization');
    $apiToken = explode(' ', $authorization);
    $user = User::where('api_token', $apiToken[1])->first();

    // Validating Post ID
    if (!$postId) {
      $model_response = [
        "status" => false,
        "message" => "Post id is required!",
        "data" => null,
      ];
      return response()->json($model_response, 400);
    }

    /**
     * Get Data Post by Id & user_id
     * Check if exists, progress the update
     * if doesn't exist, return the error message
     */
    $post = Post::with('author')->where('id', $postId)->where('user_id', $user->id)->first();

    if ($post) {
      // Title
      $title = $post->title;
      if ($request->input("title")) {
        $title = $request->input("title");
      }
      // Content
      $content = $post->content;
      if ($request->input("content")) {
        $content = $request->input("content");
      }
      // Slug
      $slug = $post->slug;
      if ($title) {
        $slug = slugify($title);
      }
      // Thumbnail
      $path_thumbnail = './post/image/';
      $name_thumbnail = $post->thumbnail;
      if ($request->hasFile("thumbnail")) {
        $name_thumbnail = 'ut-' . $user->id . time() . '.' . $request->file("thumbnail")->getClientOriginalExtension();
      }
      // Updating Post to DB Server
      $postUpdate = Post::where('id', $postId)->update([
        'title' => $title,
        'slug' => $slug,
        'thumbnail' => $name_thumbnail,
        'content' => $content,
        'user_id' => $user->id,
      ]);

      /**
       * Updating File Thumbnail from directory
       * Delete File & Upload new File
       */
      if ($name_thumbnail !== $post->thumbnail) {
        unlink($path_thumbnail . $post->thumbnail);
        if ($request->file("thumbnail")->move($path_thumbnail, $name_thumbnail)) {
          $postUpdate->thumbnail = $this->baseImage() . $name_thumbnail;
        }
      }

      // Override new data & replace thumbnail with full path
      $post = Post::with('author')->where('id', $postId)->where('user_id', $user->id)->first();
      $post->thumbnail = $this->baseImage() . $name_thumbnail;

      return response()->json([
        "status" => true,
        "message" => "Update User Post Success!",
        "data" => $post,
      ], 201);
    } else {
      return response()->json([
        "status" => false,
        "message" => "Update User Post Fail, Post not found!",
        "data" => null,
      ], 404);
    }
  }

  /**
   * Function to handler Delete User Post by ID
   */
  function delete(Request $request, $postId)
  {
    // GET USER BY API TOKEN
    $authorization = $request->header('Authorization');
    $apiToken = explode(' ', $authorization);
    $user = User::where('api_token', $apiToken[1])->first();

    // Validating Post ID
    if (!$postId) {
      $model_response = [
        "status" => false,
        "message" => "Post id is required!",
        "data" => null,
      ];
      return response()->json($model_response, 400);
    }

    /**
     * Get Data Post by Id & user_id
     * Check if exist, delete file thumbnail & delete data post by Id
     * if doesn't exist, return the error message
     */
    $post = Post::where('id', $postId)->where('user_id', $user->id)->first();
    if ($post) {
      $path_thumbnail = './post/image/';
      $file_path = $path_thumbnail . $post->thumbnail;

      if (unlink($file_path)) {
        $post->delete();
        $post_all = Post::where('user_id', $user->id)->get();

        return response()->json([
          "status" => true,
          "message" => "Delete User Post Success!",
          "data" => $post_all,
        ], 200);
      } else {
        return response()->json([
          "status" => false,
          "message" => "Delete User Post Fail, Error when deleting thumbnail!",
          "data" => null,
        ], 500);
      }
    } else {
      return response()->json([
        "status" => true,
        "message" => "Post not found!",
        "data" => null,
      ], 400);
    }
  }

  /**
   * Function to handler Get List All Post
   */
  function list()
  {
    /**
     * Get List Post
     * Replacing thumbnail with full path
     */
    $post = Post::with('author')->take(10)->get();
    foreach ($post as $postItem) {
      $postItem->thumbnail = $this->baseImage() . $postItem->thumbnail;
    }

    return response()->json([
      "status" => true,
      "message" => "Get List All Post Success!",
      "data" => $post,
    ], 200);
  }

  /**
   * Function to handler Get Detail Post by ID
   */
  function detail($postId)
  {
    /**
     * Get Data Post by Id
     * Check if exist, Replacing thumbnail with full path
     */
    $post = Post::with('author')->where('id', $postId)->first();

    if ($post) {
      $post->thumbnail = $this->baseImage() . $post->thumbnail;
      return response()->json([
        "status" => true,
        "message" => "Get Detail Post Success!",
        "data" => $post,
      ], 200);
    } else {
      return response()->json([
        "status" => false,
        "message" => "Post not found!",
        "data" => null,
      ], 404);
    }
  }
}

/**
 * Function to check is image
 *
 * @return status boolean
 */
function is_image($thumbnail)
{
  $file_name = $thumbnail->getClientOriginalName();
  $file_name_arr = explode('.', $file_name);
  $file_ext = strtolower(end($file_name_arr));
  $file_size = $thumbnail->getSize();

  $expensions = array("jpeg", "jpg", "png");

  if (in_array($file_ext, $expensions) === false) {
    return false;
  }

  if ($file_size > 2097152) {
    return false;
  }

  return true;
}

/**
 * Function to create slug from title
 *
 * @return slug
 */
function slugify($text, string $divider = '-')
{
  // replace non letter or digits by divider
  $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);
  // trim
  $text = trim($text, $divider);
  // remove duplicate divider
  $text = preg_replace('~-+~', $divider, $text);
  // lowercase
  $text = strtolower($text);

  $text = $text . '-' . Str::random(8);

  if (empty($text)) {
    return 'n-a';
  }

  return $text;
}
