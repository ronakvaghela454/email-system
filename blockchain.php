<?php

class Blockchain
{
    private string $file;

    public function __construct()
    {
        $this->file = __DIR__ . "/data/blocks.json";

        if (!file_exists($this->file)) {
            if (!is_dir(__DIR__ . "/data")) {
                mkdir(__DIR__ . "/data", 0777, true);
            }
            file_put_contents($this->file, json_encode([], JSON_PRETTY_PRINT));
        }
    }

    public function getChain(): array
    {
        $content = file_get_contents($this->file);
        $chain = json_decode($content, true);
        return is_array($chain) ? $chain : [];
    }

    public function addBlock(array $data): void
    {
        $chain = $this->getChain();
        $previousHash = count($chain) > 0 ? end($chain)['hash'] : "0";

        $block = [
            "index" => count($chain) + 1,
            "timestamp" => date("Y-m-d H:i:s"),
            "data" => $data,
            "previous_hash" => $previousHash
        ];

        $block["hash"] = hash("sha256", json_encode($block));

        $chain[] = $block;

        file_put_contents($this->file, json_encode($chain, JSON_PRETTY_PRINT));
    }

    public function validateChain(): bool
    {
        $chain = $this->getChain();

        if (count($chain) <= 1) {
            return true;
        }

        for ($i = 1; $i < count($chain); $i++) {
            $current = $chain[$i];
            $previous = $chain[$i - 1];

            if (($current['previous_hash'] ?? '') !== ($previous['hash'] ?? '')) {
                return false;
            }

            $rebuild = [
                "index" => $current["index"],
                "timestamp" => $current["timestamp"],
                "data" => $current["data"],
                "previous_hash" => $current["previous_hash"]
            ];

            $recalculatedHash = hash("sha256", json_encode($rebuild));

            if ($recalculatedHash !== ($current["hash"] ?? '')) {
                return false;
            }
        }

        return true;
    }
}
?>