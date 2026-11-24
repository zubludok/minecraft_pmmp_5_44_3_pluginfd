<?php

declare(strict_types=1);

namespace pluginfd\variant;

use InvalidArgumentException;

final class BoneGroupProcessor{

    /**
     * @return array{
     *     states: array<string, array<string, mixed>>,
     *     visibility: array<string, string>,
     *     debug: array<string, array<string, mixed>>
     * }
     */
    public static function process(
        array $groups,
        string $statePrefix = "pluginfd:"
    ): array{

        $states = [];
        $visibility = [];
        $usedBones = [];
        $debugActions = [];

        /*
         * Первый проход:
         * собираем все состояния.
         */
        foreach($groups as $groupName => $group){

            $type = $group["type"] ?? null;
            $state = $group["state"] ?? null;
            $bones = $group["bones"] ?? null;

            if(!is_string($type) || !is_string($state) || $state === ""){
                throw new InvalidArgumentException(
                    "Invalid definition of bone group '$groupName'"
                );
            }

            if(!is_array($bones) || count($bones) === 0){
                throw new InvalidArgumentException(
                    "Bone group '$groupName' has no bones"
                );
            }

            foreach($bones as $bone){
                if(!is_string($bone) || $bone === ""){
                    throw new InvalidArgumentException(
                        "Invalid bone name in group '$groupName'"
                    );
                }

                if(isset($usedBones[$bone])){
                    throw new InvalidArgumentException(
                        "Bone '$bone' is already used by group '{$usedBones[$bone]}'"
                    );
                }

                $usedBones[$bone] = $groupName;
            }

            switch($type){

                case "required_choice":
                    self::registerState($states, $state, [
                        "type" => "int",
                        "min" => 0,
                        "max" => count($bones) - 1,
                        "default" => 0
                    ]);
                    break;
					
				case "required_direction":
					if(count($bones) !== 4){
						throw new InvalidArgumentException(
							"Direction group '$groupName' must have exactly four bones"
						);
					}

					self::registerState($states,$state,[
						"type"=>"int",
						"min"=>0,
						"max"=>3,
						"default"=>0
					]);
					break;
					
                case "optional_choice":
                    self::registerState($states, $state, [
                        "type" => "int",
                        "min" => 0,
                        "max" => count($bones),
                        "default" => 0
                    ]);
                    break;

                case "linked_optional":
                    $presenceState = $group["presence_state"] ?? null;

                    if(!is_string($presenceState) || $presenceState === ""){
                        throw new InvalidArgumentException(
                            "Linked group '$groupName' has no presence_state"
                        );
                    }

                    self::registerState($states, $presenceState, [
                        "type" => "bool",
                        "default" => true
                    ]);
                    break;

                case "independent":
                    $count = count($bones);

                    if($count > 30){
                        throw new InvalidArgumentException(
                            "Independent group '$groupName' has too many bones"
                        );
                    }

                    $max = (1 << $count) - 1;

                    self::registerState($states, $state, [
                        "type" => "int",
                        "min" => 0,
                        "max" => $max,
                        "default" => $max
                    ]);
                    break;

                default:
                    throw new InvalidArgumentException(
                        "Unknown bone group type '$type'"
                    );
            }
        }

        /*
         * Второй проход:
         * проверяем ссылки и строим условия видимости.
         */
        foreach($groups as $groupName => $group){

            $type = $group["type"];
            $state = $group["state"];
            $bones = $group["bones"];

            $stateExpression =
                "q.block_state('" . $statePrefix . $state . "')";

            switch($type){

                case "required_choice":
                    foreach($bones as $index => $bone){
                        $visibility[$bone] =
                            "($stateExpression == $index)";
                    }
                    break;
				
				case "required_direction":
					foreach($bones as $index=>$bone){
						$visibility[$bone]="($stateExpression == $index)";
					}
					break;

                case "optional_choice":
                    foreach($bones as $index => $bone){
                        $visibility[$bone] =
                            "($stateExpression == " . ($index + 1) . ")";
                    }
                    break;

                case "linked_optional":

                    if(!isset($states[$state])){
                        throw new InvalidArgumentException(
                            "Linked group '$groupName' references unknown state '$state'"
                        );
                    }

                    $definition = $states[$state];

                    $offset = $group["value_offset"] ?? 0;

                    if(!is_int($offset) || $offset < 0){
                        throw new InvalidArgumentException(
                            "Invalid value_offset in linked group '$groupName'"
                        );
                    }

                    $requiredMax = count($bones) - 1 + $offset;

                    if(
                        $definition["type"] !== "int" ||
                        $definition["min"] > $offset ||
                        $definition["max"] < $requiredMax
                    ){
                        throw new InvalidArgumentException(
                            "State '$state' is incompatible with linked group '$groupName'"
                        );
                    }

                    $presenceState = $group["presence_state"];

                    $presenceExpression =
                        "q.block_state('" . $statePrefix . $presenceState . "')";

                    foreach($bones as $index => $bone){
                        $value = $index + $offset;

                        $visibility[$bone] =
                            "($presenceExpression && $stateExpression == $value)";
                    }
                    break;

                case "independent":
                    foreach($bones as $index => $bone){
                        $bit = 1 << $index;

                        $visibility[$bone] =
                            "(math.mod(math.floor($stateExpression / $bit), 2) == 1)";
                    }
                    break;
            }
        }

        /*
         * Третий проход:
         * собираем отладочные действия.
         */
        foreach($groups as $groupName => $group){

            $debug = $group["debug"] ?? null;

            if($debug === null){
                continue;
            }

            if(!is_array($debug)){
                throw new InvalidArgumentException(
                    "Invalid debug configuration for group '$groupName'"
                );
            }

            $tool = $debug["tool"] ?? null;
            $action = $debug["action"] ?? null;

            if(!in_array($tool, ["stick", "axe", "shovel", "sword", "hoe"], true)){
                throw new InvalidArgumentException(
                    "Unknown debug tool in group '$groupName'"
                );
            }

            if(!in_array($action, ["cycle", "toggle", "toggle_all"], true)){
                throw new InvalidArgumentException(
                    "Unknown debug action in group '$groupName'"
                );
            }

            // linked_optional управляет presence_state.
            $targetState = $group["type"] === "linked_optional"
                ? $group["presence_state"]
                : $group["state"];

            if(!isset($states[$targetState])){
                throw new InvalidArgumentException(
                    "Debug group '$groupName' references unknown state '$targetState'"
                );
            }

            $definition = $states[$targetState];

            if(
                $action === "cycle" &&
                $definition["type"] !== "int"
            ){
                throw new InvalidArgumentException(
                    "Cycle action requires an integer state in group '$groupName'"
                );
            }

            if(
                $action === "toggle" &&
                $definition["type"] !== "bool"
            ){
                throw new InvalidArgumentException(
                    "Toggle action requires a boolean state in group '$groupName'"
                );
            }

            if(
                $action === "toggle_all" &&
                ($group["type"] !== "independent" || $definition["type"] !== "int")
            ){
                throw new InvalidArgumentException(
                    "Toggle_all requires an independent group '$groupName'"
                );
            }

            $debugActions[$groupName] = [
                "tool" => $tool,
                "action" => $action,
                "state" => $targetState,
                "min" => $definition["min"] ?? 0,
                "max" => $definition["max"] ?? 1
            ];
        }

        return [
            "states" => $states,
            "visibility" => $visibility,
            "debug" => $debugActions
        ];
    }

    private static function registerState(
        array &$states,
        string $name,
        array $definition
    ): void{

        if(isset($states[$name])){
            if($states[$name] !== $definition){
                throw new InvalidArgumentException(
                    "Conflicting definitions for state '$name'"
                );
            }

            return;
        }

        $states[$name] = $definition;
    }
}