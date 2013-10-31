## Harvey is separate validation for Laravel 4

This is my internal project, not yet complete.

### Installation

To get the lastest version of Theme simply require it in your `composer.json` file.

~~~
"teepluss/harvey": "dev-master"
~~~

You'll then need to run `composer install` to download it and have the autoloader updated.

## Usage

~~~php
class Blog extends \Teepluss\Harvey\Harvey {

    /**
     * Define rules.
     *
     * @type array
     */
    public static $rules = array(
        'description' => 'min:10|max:500',
        'onCreate'    => array(
            'title' => 'required',
            'url' => 'active_url'
        ),
        'onUpdate'    => array(
            'title' => 'required'
        )
    );

    /**
     * Custom validation messages.
     *
     * @type array
     */
    public static $messages = array(
        'title.required' => 'Please fill title before submitting.'
    );

    /**
     * Event before validate.
     *
     * @return voide
     */
    protected function beforeValidate()
    {
        /*$validator->sometimes('description', 'numeric', function($input)
        {
            return $input->title == 'tee';
        });*/
    }

}
~~~

### This code for creating a new content.

~~~php
$blog = new Blog;

$blog->title = 'New blog';
$blog->description = 'This is my first entry';
$blog->url = 'http://www.domain.com';

// Addition rule for another input.
/*$blog->addValidate(
    array('other' => Input::get('other')),
    array('other' => 'required|email'),
    array('other.required' => 'sss')
);*/

if ( ! $blog->save())
{
    $errors = $blog->errors();

    return Redirect::back()->withErrors($errors)->withInput();
}
~~~

### Validation rules for creating.

~~~php
array(3) [
    'description' => array(2) [
        string (6) "min:10"
        string (7) "max:500"
    ]
    'title' => array(1) [
        string (8) "required"
    ]
    'url' => array(1) [
        string (10) "active_url"
    ]
]
~~~

### This code for updating an exists content.

~~~php
$blog = Blog::find(1);

$blog->title = 'New blog';
$blog->description = 'This is my first entry';
$blog->url = 'http://www.domain.com';

$blog->save();

if ( ! $blog->save())
{
    $errors = $blog->errors();

    return Redirect::back()->withErrors($errors)->withInput();
}
~~~

### Validation rules for updating.

~~~php
array(2) [
    'description' => array(2) [
        string (6) "min:10"
        string (7) "max:500"
    ]
    'title' => array(1) [
        string (8) "required"
    ]
]
~~~

## Support or Contact

If you have some problem, Contact teepluss@gmail.com


[![Support via PayPal](https://rawgithub.com/chris---/Donation-Badges/master/paypal.jpeg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9GEC8J7FAG6JA)