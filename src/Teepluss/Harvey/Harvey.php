<?php namespace Teepluss\Harvey;

use Closure;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Input;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

abstract class Harvey extends Model {

    /**
     * Validation rules.
     *
     * @var array
     */
    public static $rules = array();

    /**
     * Validation custom messages.
     *
     * @var array
     */
    public static $messages = array();

    /**
     * Addition rules.
     *
     * @var array
     */
    protected $addition = array();

    /**
     * Validator.
     *
     * @var \Illuminate\Validation\Validators
     */
    protected $validator;

    /**
     * Validation errors.
     *
     * @var \Illuminate\Support\MessageBag
     */
    protected $errors;

    /**
     * New instance for Harvey.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        // Validator.
        $this->validator = \App::make('validator');

        // Message bag.
        $this->errors = new MessageBag;
    }

    /**
     * Model event for validation.
     *
     * @return mixed
     */
    public static function boot()
    {
        parent::boot();

        // Validation rules.
        $rules = static::$rules;

        // Model event fired.
        static::saving(function($model) use ($rules)
        {
            $event = $model->exists ? 'update' : 'create';

            // Transform validate rules to Laravel rules.
            $rules = $model->transform($event, $rules);

            // Let Harvey validate.
            if ( ! $model->validate($rules))
            {
                return false;
            }
        });
    }

    /**
     * Model validation.
     *
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $inputs
     * @return boolean
     */
    public function validate(array $rules, array $messages = array(), array $inputs = array())
    {
        // Define passed state.
        $passed = true;

        // Validation custom messages.
        $messages = $messages ?: static::$messages;

        // Input fill in.
        $inputs = ($inputs) ?: $this->getDirty() + $this->getAttributes();

        foreach (array('inputs', 'rules', 'messages') as $add)
        {
            if (isset($this->addition[$add]))
            {
                ${$add} = array_merge(${$add}, $this->addition[$add]);
            }
        }

        // Validator instance.
        $validator = $this->validator->make($inputs, $rules, $messages);

        // Fire event before validate.
        $this->beforeValidate($validator);

        if ($validator->fails())
        {
            $passed = false;

            $this->errors = $validator->messages();
        }

        // Unset addition rules.
        $this->addition = array();

        return $passed;
    }

    /**
     * Hook before validate.
     *
     * This is work with validate sometimes.
     *
     * @param  Validator $validator
     * @return void
     */
    protected function beforeValidate($validator)
    {
        // $validator->sometimes('tax', 'required|numeric', function($input)
        // {
        //     return $input->amount >= 100;
        // });
    }

    /**
     * Addition validate rules.
     *
     * @param mixed  $inputs
     * @param array  $rules
     * @param array  $messages
     */
    public function addValidate($inputs, array $rules = array(), array $messages = array())
    {
        $this->addition['inputs'] = $inputs;

        $this->addition['rules'] = $rules;

        $this->addition['messages'] = $messages;
    }

    /**
     * Get error messages.
     *
     * @return  \Illuminate\Support\MessageBag
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Transform separate validation to Laravel format.
     *
     * @param  array  $rules
     * @param  array  $laws
     * @return array
     */
    public function transform($event, array $rules, &$laws = array())
    {
        // Compare with changed fields.
        $changes = $this->getDirty();

        foreach ($rules as $key => $rule)
        {
            if (preg_match('|on([A-Z][a-z]+)|', $key, $matches))
            {
                $on = strtolower($matches[1]);

                if ($on == $event)
                {
                    $this->transform($event, $rule, $laws);
                }
            }
            else
            {
                // No reason to validate clean inputs.
                if ( ! array_key_exists($key, $changes)) continue;

                // Changing rules to array.
                $rule = is_array($rule) ? $rule : explode('|', $rule);

                // Merge rule to existing field.
                if (array_key_exists($key, $laws))
                {
                    $rule = array_merge($laws[$key], $rule);

                    $rule = array_unique($rule);
                }

                $laws[$key] = $rule;
            }
        }

        return $laws;
    }

}