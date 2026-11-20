<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\FixerFactory;
use PhpCsFixer\RuleSet\RuleSet;
use Symplify\EasyCodingStandard\Config\ECSConfig;

// ECS ne fournit plus d'ensemble « Symfony » : on reprend tel quel le jeu de règles
// @Symfony de php-cs-fixer (embarqué par ECS), utilisé avant la migration, pour que
// le style du code reste le même.
$ruleSet = new RuleSet(['@Symfony' => true]);
$fixers = (new FixerFactory())->registerBuiltInFixers()->useRuleSet($ruleSet)->getFixers();

$config = ECSConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withRootFiles();

// withRules() remplace la liste à chaque appel : on la passe en une fois.
$rules = [];
foreach ($fixers as $fixer) {
    $configuration = $ruleSet->getRuleConfiguration($fixer->getName());
    if (null === $configuration) {
        $rules[] = $fixer::class;
    } else {
        $config->withConfiguredRule($fixer::class, $configuration);
    }
}

// En plus de @Symfony : declare(strict_types=1) dans chaque fichier.
$rules[] = DeclareStrictTypesFixer::class;

// En plus de @Symfony : fonctions natives préfixées par « \ » (\strtr()). Dans un
// namespace, PHP 8.4+ compile un appel non qualifié avec un saut (JMP_FRAMELESS)
// qui cherche d'abord une fonction du namespace courant : Xdebug le compte comme
// une branche, jamais empruntée, ce qui fausse la couverture des chemins (#8).
$config->withConfiguredRule(NativeFunctionInvocationFixer::class, [
    'include' => ['@all'],
    'scope' => 'namespaced',
    'strict' => true,
]);

return $config->withRules($rules);
