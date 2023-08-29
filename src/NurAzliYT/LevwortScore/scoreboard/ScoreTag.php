<?php
declare(strict_types = 1);

namespace NurAzliYT\LevwortScore\scoreboard;

class ScoreTag {

	public function __construct(
		private string $name,
		private string $value = ""
	) {}

	public function getId(): string {
		return "{" . $this->name . "}";
	}

	public function getName(): string {
		return $this->name;
	}

	public function getValue(): string {
		return $this->value;
	}

	public function setValue(string $value): void {
		$this->value = $value;
	}

	public function __toArray(): array {
		return [
			"name"  => $this->name,
			"value" => $this->value
		];
	}
}
