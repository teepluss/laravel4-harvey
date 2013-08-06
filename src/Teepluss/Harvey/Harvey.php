<?php namespace Teepluss\Harvey;

use Closure;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Input;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

abstract class Harvey extends Model {

    public static $rules = array();

    public static $messages = array();

    protected $errors;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        // Message bag.
        $this->errors = new MessageBag;
    }

    public function validate(array $rules, array $messages = array(), array $inputs = array())
    {
        // Define passed state.
        $passed = true;

        // Validation custom messages.
        $messages = ($messages) ?: static::$messages;

        // Input fill in.
        $inputs = ($inputs) ?: $this->getDirty();

        //sd($rules);

        // Validator instance.
        $validator = Validator::make($inputs, $rules, $messages);

        if ($validator->fails())
        {
            $passed = false;

            $this->errors = $validator->messages();
        }

        return $passed;
    }

    public function errors()
    {
        return $this->errors;
    }

    protected function transform($rules, &$laws = array())
    {
        // Event.
        $event = $this->exists ? 'update' : 'create';

        // Compare with changed fields.
        $changes = $this->getDirty();

        foreach ($rules as $key => $rule)
        {
            if (preg_match('|on([A-Z][a-z]+)|', $key, $matches))
            {
                $on = strtolower($matches[1]);

                if ($on == $event)
                {
                    $this->transform($rule, $laws);
                }
            }
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

        return $laws;
    }

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

    public function save(array $options = array())
    {
        return $this->internalSave($options);
    }

}