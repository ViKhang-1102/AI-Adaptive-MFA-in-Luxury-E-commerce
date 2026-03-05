<?php
$dir = __DIR__ . '/resources/views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $original = $content;
        
        $replacements = [
            'bg-blue-600 text-white hover:bg-blue-700' => 'bg-primary text-white shadow-soft transition-all duration-300 hover:bg-primary-light hover:shadow-hover hover:-translate-y-0.5',
            'bg-blue-600 text-white' => 'bg-primary text-white shadow-soft transition-all duration-300 hover:shadow-hover hover:-translate-y-0.5',
            'hover:bg-blue-700' => 'hover:bg-primary-light hover:-translate-y-0.5',
            'bg-blue-600' => 'bg-primary shadow-soft transition-all duration-300',
            'text-blue-600' => 'text-primary',
            'hover:text-blue-700' => 'hover:text-primary-light',
            'focus:ring-blue-500' => 'focus:ring-gold',
            'focus:border-blue-500' => 'focus:border-gold',
            'border-blue-600' => 'border-gold',
            'hover:border-blue-500' => 'hover:border-gold',
            'hover:border-blue-600' => 'hover:border-gold',
            'border-gray-300' => 'border-neutral-200',
            'border-gray-200' => 'border-neutral-100',
            'border-gray-100' => 'border-neutral-50',
            'bg-gray-50' => 'bg-neutral-50',
            'bg-gray-100' => 'bg-neutral-100',
            'bg-gray-200' => 'bg-neutral-200',
            'bg-gray-800' => 'bg-primary',
            'text-gray-500' => 'text-neutral-500',
            'text-gray-600' => 'text-neutral-600',
            'text-gray-700' => 'text-neutral-700',
            'text-gray-800' => 'text-primary',
            'text-gray-900' => 'text-primary-dark',
        ];
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        
        // Specific word replacements to avoid overriding partial matches
        $content = preg_replace('/\brounded\b/', 'rounded-md', $content);
        $content = preg_replace('/\bshadow\b/', 'shadow-sm', $content);
        
        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
        }
    }
}
echo "Refactor complete.\n";
