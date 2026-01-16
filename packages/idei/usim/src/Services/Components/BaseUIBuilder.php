<?php

namespace Idei\Usim\Services\Components;

use Idei\Usim\Services\Support\UIIdGenerator;

abstract class BaseUIBuilder
{
    protected int $id;
    protected ?string $name;
    protected array $config = [];
    protected string $type;

    public function __construct(?string $name = null)
    {
        $this->name = $name;

        // Detectar automáticamente el contexto desde la clase que invoca
        $context = $this->detectCallingContext();
        
        // Usar el generador centralizado de IDs
        // Si tiene nombre, generar ID determinístico basado en el nombre
        if ($name !== null) {
            $this->id = UIIdGenerator::generateFromName($context, $name);
        } else {
            $this->id = UIIdGenerator::generate($context);
        }
        
        $this->type = $this->getTypeFromClassName();
        $this->config = array_merge([
            'type' => $this->type,
            'visible' => true,
        ], $this->getDefaultConfig());
        
        // Only include 'name' if it's not null
        if ($this->name !== null) {
            $this->config['name'] = $this->name;
        }
    }

    /**
     * Detecta automáticamente la clase que está invocando el builder
     * Busca en el stack trace la primera clase fuera del namespace UI
     * 
     * @return string El nombre completo con namespace de la clase invocante
     */
    private function detectCallingContext(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        
        // Buscar en el stack trace la primera clase que NO sea del namespace UI
        foreach ($trace as $frame) {
            if (isset($frame['class']) && 
                !str_starts_with($frame['class'], 'App\\Services\\UI\\')) {
                return $frame['class']; // Retornar nombre completo con namespace
            }
        }
        
        return 'default';
    }

    private function getTypeFromClassName(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();
        // Extrae la palabra antes de "Builder" y la convierte a minúsculas
        // Ej: "ButtonBuilder" -> "button", "LabelBuilder" -> "label"
        return strtolower(str_replace('Builder', '', $className));
    }

    abstract protected function getDefaultConfig(): array;

    public function visible(bool $visible = true): self
    {
        $this->config['visible'] = $visible;
        return $this;
    }

    public function name(?string $name): self
    {
        $this->name = $name;
        if ($name !== null) {
            $this->config['name'] = $name;
        } else {
            unset($this->config['name']);
        }
        return $this;
    }

    public function build(): array
    {
        return [$this->id => $this->config];
    }
    
    /**
     * Método de utilidad para debugging - obtiene información del contexto
     * 
     * @param string $context Nombre del contexto
     * @return array Información del contexto (offset, contador, etc)
     */
    public static function getContextInfo(string $context): array
    {
        return UIIdGenerator::getContextInfo($context);
    }
}