## Domain Registra API Wrapper

This is my internal project, not yet complete.

### Installation

To get the lastest version of Theme simply require it in your `composer.json` file.

~~~
"repositories" : [
    {
        "type": "vcs",
        "url": "https://github.com/teepluss/laravel4-harvey"
    }
],
"require": {
    "teepluss/harvey": "dev-master"
}
~~~

You'll then need to run `composer install` to download it and have the autoloader updated.

##Usage

~~~php
class Blog extends \Teepluss\Harvey\Harvey {

    public static $rules = array(
        'title'       => 'required',
        'description' => 'min:20|max:500',
        'onCreate'    => array(
            'title' => 'unique|emails'
        ),
        'onUpdate'    => array(
            'description' => 'required_with:title'
        )
    );

}
~~~

~~~php
$blog = new Blog;

$blog->title = Input::get('title');
$blog->description = Input::get('description');

if ( ! $blog->save())
{
    $errors = $blog->errors();

    return Redirect::back()->withErrors($errors)->withInput();
}
~~~