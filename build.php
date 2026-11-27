<?php
$pharName = 'pluginfd.phar';
$pharPath = 'pluginfd.phar';

if (file_exists($pharPath)) {	// Удаляем старый phar-файл если существует
    unlink($pharPath);
    echo "✓ Удален старый phar-файл\n";
}

$phar = new Phar($pharPath);	// Создаем новый phar
$phar->setStub('<?php __HALT_COMPILER(); ?>');

// Список основных файлов для добавления
$filesToAdd = [
    'plugin.yml',
    'src/pluginfd/pluginfd.php',
    'src/pluginfd/block/ConfiguredBlock.php',
    'src/pluginfd/block/ConfiguredResourceBlock.php',
    'src/pluginfd/block/ConfiguredTreeBlock.php',
    'src/pluginfd/block/TrashBlock.php',
    'src/pluginfd/block/VoxelPlaceholderBlock.php',
    'src/pluginfd/interaction/ConfiguredBlockInteraction.php',
    'src/pluginfd/interaction/ConfiguredResourceBlockInteraction.php',
    'src/pluginfd/interaction/ConfiguredTreeBlockInteraction.php',
    'src/pluginfd/item/SocialButton.php',
    'src/pluginfd/item/VoxelPlaceItem.php',
    'src/pluginfd/listener/DebugListenerBlock.php',
    'src/pluginfd/listener/VoxelPlaceListener.php',
    'src/pluginfd/material/BlockMaterialCatalog.php',
    'src/pluginfd/material/ResourceMaterialCatalog.php',
    'src/pluginfd/material/TreeMaterialCatalog.php',
    'src/pluginfd/registration/BlockRegistrar.php',
    'src/pluginfd/registration/ResourceBlockRegistrar.php',
    'src/pluginfd/registration/TreeBlockRegistrar.php',
    'src/pluginfd/variant/BlockVariantDefinition.php',
    'src/pluginfd/variant/ResourceVoxelCatalog.php',
    'src/pluginfd/variant/TreeVariantCatalog.php',
    'src/pluginfd/variant/VoxelCatalog.php',
    'src/pluginfd/variant/BoneGroupProcessor.php',
];

// 1. Проверяем и добавляем основные файлы
echo "\n--- Основные файлы ---\n";
foreach($filesToAdd as $file) {
    if(file_exists($file)) {
        $phar->addFile($file);
        $added[] = $file;
        echo "✓ Добавлен: $file\n";
    } else {
        $missing[] = $file;
        echo "✗ ПРОПУЩЕН: $file (файл не найден)\n";
    }
}

echo "\n--- Вторичные файлы ---\n";

// 2. Добавляем resources с сохранением структуры папок
$resourcesDir = $baseDir . '/resources';
if (is_dir($resourcesDir)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($resourcesDir, FilesystemIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $relativePath = 'resources/' . $iterator->getSubPathname();
            $phar->addFile($file->getPathname(), $relativePath);
            echo "Добавлен: $relativePath\n";
        }
    }
}

// Устанавливаем stub
$phar->setStub(
    "<?php
    require_once('phar://' . __FILE__ . 'src/pluginfd/pluginfd.php');
    __HALT_COMPILER();
    ?>"
);

$phar->stopBuffering();

echo "\n✅ Phar архив создан: $pharName\n";
echo "Размер: " . round(filesize($pharPath) / 1024, 2) . " KB\n";