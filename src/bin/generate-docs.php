<?php

require __DIR__ . '/../../vendor/autoload.php';

$input = $argv[1] ?? null;
$output = $argv[2] ?? null;

if (empty($input) || empty($output) || !file_exists($input)) {
    throw new \Exception('Could not find source');
}

$basename = basename($input);
$info = pathinfo($input);
$className = $info['filename'];
$namespace = str_replace('/', '\\', preg_replace('/^(.\/)?src\//', '', $info['dirname']));

require $input;
$reflection = new ReflectionClass('markhuot\\craftpest\\' . $namespace . '\\' . $className);

$contents = [];
$contents[] = parseComment($reflection->getDocComment());

foreach ($reflection->getMethods() as $method) {
    if ($method->getDeclaringClass()->getName() === $reflection->getName() && $comment = $method->getDocComment()) {
        $comment = parseComment($method->getDocComment());
        if (!empty($comment)) {
            $contents[] = '## ' . $method->getName() . "()\n" . $comment;
        }
    }
}

function parseComment(string $comment)
{
    $comment = preg_replace('/^\/\*\*/', '', $comment);
    $comment = preg_replace('/^\s*\*\s@\w+.*$/m', '', $comment);
    $comment = preg_replace('/^\s*\*\s/m', '', $comment);
    $comment = preg_replace('/\*\/$/m', '', $comment);
    $comment = preg_replace('/(^\s+|\s+$)/', '', $comment);

    return $comment;
}

if (!is_dir(dirname($output))) {
    mkdir(dirname($output), 0777, true);
}
file_put_contents($output, implode("\n\n", $contents));
