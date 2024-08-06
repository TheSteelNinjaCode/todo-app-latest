<?php

namespace Lib;

/**
 * Manages the application state.
 */
class StateManager
{
    private const APP_STATE = 'app_state_F989A';
    private array $state = [];
    private array $listeners = [];

    /**
     * Initializes a new instance of the StateManager class.
     */
    public function __construct()
    {
        global $isWire;

        $this->loadState();

        if (!$isWire) {
            $this->resetState();
        }
    }

    /**
     * Gets the state value for the specified key.
     *
     * @param string|null $key The key of the state value to get.
     * @param mixed $initialValue The initial value to set if the key does not exist.
     * @return mixed The state value for the specified key.
     */
    public function getState(string $key = null, mixed $initialValue = null): mixed
    {
        if ($key === null) {
            return new \ArrayObject($this->state, \ArrayObject::ARRAY_AS_PROPS);
        }

        $value = $this->state[$key] ?? $initialValue;

        return is_array($value) ? new \ArrayObject($value, \ArrayObject::ARRAY_AS_PROPS) : $value;
    }

    /**
     * Sets the state value for the specified key.
     *
     * @param string $key The key of the state value to set.
     * @param mixed $value The value to set.
     */
    public function setState(string $key, mixed $value = null): void
    {
        if (array_key_exists($key, $GLOBALS)) {
            $GLOBALS[$key] = $value;
        }

        $this->state[$key] = $value;

        $this->notifyListeners();
        $this->saveState();
    }

    /**
     * Subscribes a listener to state changes.
     *
     * @param callable $listener The listener function to subscribe.
     * @return callable A function that can be called to unsubscribe the listener.
     */
    public function subscribe(callable $listener): callable
    {
        $this->listeners[] = $listener;
        $listener($this->state);
        return fn () => $this->listeners = array_filter($this->listeners, fn ($l) => $l !== $listener);
    }

    /**
     * Saves the current state to storage.
     */
    private function saveState(): void
    {
        $_SESSION[self::APP_STATE] = json_encode($this->state, JSON_THROW_ON_ERROR);
    }

    /**
     * Loads the state from storage, if available.
     */
    private function loadState(): void
    {
        if (isset($_SESSION[self::APP_STATE])) {
            $loadedState = json_decode($_SESSION[self::APP_STATE], true, 512, JSON_THROW_ON_ERROR);
            if (is_array($loadedState)) {
                $this->state = $loadedState;
                $this->notifyListeners();
            }
        }
    }

    /**
     * Resets the application state to an empty array.
     */
    public function resetState(): void
    {
        $this->state = [];
        $this->notifyListeners();
        $this->saveState();
    }

    /**
     * Notifies all listeners of state changes.
     */
    private function notifyListeners(): void
    {
        foreach ($this->listeners as $listener) {
            $listener($this->state);
        }
    }
}
