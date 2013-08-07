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

        // Message bag.
        $this->errors = new MessageBag;
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
        $messages = ($messages) ?: static::$messages;

        // Input fill in.
        $inputs = ($inputs) ?: $this->getDirty();

        // Validator instance.
        $validator = Validator::make($inputs, $rules, $messages);

        if ($validator->fails())
        {
            $passed = false;

            $this->errors = $validator->messages();
        }

        return $passed;
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
    protected function transform(array $rules, &$laws = array())
    {
        // Event.
        $event = $this->exists ? 'update' : 'create';

        // Compare with changed fields.
        $changes = $this->getDirty();

        foreach ($rules as $key => $rule)
        {
            // Filter events.
            if (preg_match('|on([A-Z][a-z]+)|', $key, $matches))
            {
                $on = strtolower($matches[1]);

                if ($on == $event)
                {
                    $this->transform($rule, $laws);
                }
            }
            // Transform rules.
            else
            {
                if ( ! array_key_exists($key, $changes)) continue;

                $rule = is_array($rule) ? $rule : explode('|', $rule);

                if (array_key_exists($key, $laws))
                {
                    $rule = array_merge($laws[$key], $rule);

                    $rule = array_unique($rule);
                }

                $laws[$key] = $rule;
            }
        }

        sd($laws);

        return $laws;
    }

    /**
     * Internal saving before save.
     *
     * @param  array  $options
     * @return mixed
     */
    protected function internalSave(array $options)
    {
        // Rule defined.
        $rules = static::$rules;

        // Transform format to Laravel rules.
        $laws = $this->transform($rules);

        if ($this->validate($laws))
        {
            return parent::save($options);
        }

        return false;
    }

    /**
     * Validate and save.
     *
     * @param  array  $options
     * @return mixed
     */
    public function save(array $options = array())
    {
        return $this->internalSave($options);
    }

    /**
     * Force save using original Laravel method.
     *
     * @return object
     */
    public function forceSave()
    {
        return paranet::save();
    }

}