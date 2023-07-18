<?php

namespace FreePBX\modules\Music\Api\Gql;

use GraphQL\Type\Definition\Type;
use FreePBX\modules\Api\Gql\Base;

use GraphQLRelay\Relay;

class Music extends Base {
	protected $module = 'music';

	public function queryCallback() {
		if($this->checkAllReadScope()) {
			return fn() => [
					'allMusiconholds' => [
						'type' => $this->typeContainer->get('musiconhold')->getConnectionType(),
						'description' => 'Used to manage a system wide list of blocked callers',
						'args' => Relay::connectionArgs(),
						'resolve' => fn($root, $args) => Relay::connectionFromArray($this->freepbx->Music->getCategories(), $args),
					],
					'musiconhold' => [
						'type' => $this->typeContainer->get('musiconhold')->getObject(),
						'args' => [
							'id' => [
								'type' => Type::id(),
								'description' => 'The ID',
							]
						],
						'resolve' => function($root, $args) {
							$item = $this->freepbx->Music->getCategoryByID(Relay::fromGlobalId($args['id'])['id']);
							return $item ?? null;
						}
					]
				];
		}
	}

	public function initializeTypes() {
		$user = $this->typeContainer->create('musiconhold');
		$user->setDescription('Used to manage a system wide list of blocked callers');

		$user->addInterfaceCallback(fn() => [$this->getNodeDefinition()['nodeInterface']]);

		$user->setGetNodeCallback(function($id) {
			$item = $this->freepbx->Music->getCategoryByID($id);
			return $item ?? null;
		});

		$user->addFieldCallback(fn() => [
				'id' => Relay::globalIdField('musiconhold', fn($row) => $row['id']),
				'category' => [
					'type' => Type::string(),
					'description' => 'Category Name'
				],
				'type' => [
					'type' => Type::string(),
					'description' => 'Type of Music on Hold. If set to "Files" then this category will play the files listed below. If set to "Custom Application" then this category will stream music from the set application'
				],
				'random' => [
					'type' => Type::boolean(),
					'description' => 'Enable random playback of music for this category. If disabled music will play in alphabetical order'
				],
				'application' => [
					'type' => Type::string()
				],
				'format' => [
					'type' => Type::string()
				]
			]);

		$user->setConnectionResolveNode(fn($edge) => $edge['node']);

		$user->setConnectionFields(fn() => [
				'totalCount' => [
					'type' => Type::int(),
					'resolve' => fn($value) => is_countable($this->freepbx->Music->getCategories()) ? count($this->freepbx->Music->getCategories()) : 0
				],
				'musiconholds' => [
					'type' => Type::listOf($this->typeContainer->get('musiconhold')->getObject()),
					'resolve' => function($root, $args) {
						$data = array_map(fn($row) => $row['node'],$root['edges']);
						return $data;
					}
				]
			]);
	}
}
