<?php

declare(strict_types=1);

namespace pluginfd\variant;

final class TreeVariantCatalog{

    public static function getVariants(): array{
        return [

            /* ПЕНЬ
             * Нижняя часть обязательна.
             * Верхняя часть может отсутствовать.
             * Толщина обеих частей всегда "одинаковая". */
            "stump" => [
                "name" => "Пень",
                "geometry" => "geometry.tree.stump",
                "bone_groups" => [
                    "lower" => [
                        "type" => "required_choice",
                        "state" => "thickness",
                        "bones" => [
                            "lower_1",
                            "lower_2",
                            "lower_3",
                            "lower_4",
                            "lower_5",
                            "lower_6"
                        ],
                        "debug" => [
                            "tool" => "stick",
                            "action" => "cycle"
                        ]
                    ],
                    "upper" => [
                        "type" => "linked_optional",
                        "state" => "thickness",
                        "presence_state" => "upper_present",
                        "value_offset" => 0,
                        "bones" => [
                            "upper_1",
                            "upper_2",
                            "upper_3",
                            "upper_4",
                            "upper_5",
                            "upper_6"
                        ],
                        "debug" => [
                            "tool" => "shovel",
                            "action" => "toggle"
                        ]
                    ]
                ]
            ],

			"trunk" => [
				"name" => "Ствол",
				"geometry" => "geometry.tree.trunk",
				"bone_groups" => [
					/* ТОЛЩИНА СТВОЛА
					 * Всегда один из шести вариантов.
					 * Пустого состояния нет. */
					"thickness" => [
						"type" => "required_choice",
						"state" => "thickness",
						"bones" => [
							"trunk_1",
							"trunk_2",
							"trunk_3",
							"trunk_4",
							"trunk_5",
							"trunk_6"
						],
						"debug" => [
							"tool" => "stick",
							"action" => "cycle"
						]
					],

					/* СЕВЕРНАЯ ВЕТКА
					 * 0 = отсутствует
					 * 1 = тонкая
					 * 2 = толстая */
					"branch_north" => [
						"type" => "optional_choice",
						"state" => "branch_north",
						"bones" => [
							"branch_north_thin",
							"branch_north_thick"
						]
					],

					/* ЮЖНАЯ ВЕТКА */
					"branch_south" => [
						"type" => "optional_choice",
						"state" => "branch_south",
						"bones" => [
							"branch_south_thin",
							"branch_south_thick"
						]
					],

					/* ВОСТОЧНАЯ ВЕТКА */
					"branch_east" => [
						"type" => "optional_choice",
						"state" => "branch_east",
						"bones" => [
							"branch_east_thin",
							"branch_east_thick"
						]
					],

					/* ЗАПАДНАЯ ВЕТКА */
					"branch_west" => [
						"type" => "optional_choice",
						"state" => "branch_west",
						"bones" => [
							"branch_west_thin",
							"branch_west_thick"
						]
					]
				]
			],

            /* ВЕТКИ
             * Каждая кость включается независимо. */
            "branch_sa" => [
                "name" => "Ветка",
                "geometry" => "geometry.tree.branch_sa",
                "bone_groups" => [
                    "variants" => [
                        "type" => "independent",
                        "state" => "branch_mask",
                        "bones" => [
                            "branch_a",
                            "branch_b",
                            "branch_c",
                            "branch_d",
                            "branch_e",
                            "branch_f"
                        ],
                        "debug" => [
                            "tool" => "sword",
                            "action" => "toggle_all"
                        ]
                    ]
                ]
            ],
            "branch_sb" => [
                "name" => "Ветка",
                "geometry" => "geometry.tree.branch_sb",
                "bone_groups" => [
                    "variants" => [
                        "type" => "independent",
                        "state" => "branch_mask",
                        "bones" => [
                            "branch_a",
                            "branch_b",
                            "branch_c",
                            "branch_d",
                            "branch_e",
                            "branch_f"
                        ],
                        "debug" => [
                            "tool" => "sword",
                            "action" => "toggle_all"
                        ]
                    ]
                ]
            ],
            "branch_sc" => [
                "name" => "Ветка",
                "geometry" => "geometry.tree.branch_sc",
                "bone_groups" => [
                    "variants" => [
                        "type" => "independent",
                        "state" => "branch_mask",
                        "bones" => [
                            "branch_a",
                            "branch_b",
                            "branch_c",
                            "branch_d",
                            "branch_e",
                            "branch_f"
                        ],
                        "debug" => [
                            "tool" => "sword",
                            "action" => "toggle_all"
                        ]
                    ]
                ]
            ]
        ];
    }
}