<?php

namespace LibricielTest\Password\Service;

use Libriciel\Password\PasswordException;
use Libriciel\Password\Service\PasswordGeneratorAnssi;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de la classe Libriciel\Password\Service\PasswordGeneratorAnssi.
 */
class PasswordGeneratorAnssiTest extends TestCase
{
    /**
     * Configuration par défaut.
     */
    protected array $defaultConfig = [
        'binary' => false,
        'numbers' => true,
        'lowercase_hexadecimal' => false,
        'uppercase_hexadecimal' => false,
        'lowercase_letters' => true,
        'uppercase_letters' => true,
        'symbols_1' => false,
        'symbols_2' => false,
        'lowercase_accents_fr' => false,
        'uppercase_accents_fr' => false,
    ];

    /**
     * Les caractères de chacune des classes, dans l'ordre de leur définition.
     */
    protected array $classes = [
        'binary' => '01',
        'numbers' => '0123456789',
        'lowercase_hexadecimal' => '0123456789abcdef',
        'uppercase_hexadecimal' => '0123456789ABCDEF',
        'lowercase_letters' => 'abcdefghijklmnopqrstuvwxyz',
        'uppercase_letters' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'symbols_1' => '€!#$*% ?',
        'symbols_2' => '&[|]@^µ§\:/;.,<>°²³\'"',
        'lowercase_accents_fr' => 'àâäæçèéêëîïôœùûü',
        'uppercase_accents_fr' => 'ÀÂÄÆÇÈÉÊËÎÏÔŒÙÛÜ',
    ];

    /**
     * Test de la méthode PasswordGeneratorAnssi::defaults().
     */
    public function testDefaults()
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $actual = $pwdGeneratorAnsi->defaults();
        $this->assertEquals($this->defaultConfig, $actual);
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::generate() avec la configuration
     * par défaut.
     */
    public function testGenerateDefaultConfig()
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        // Par défaut pour un mot de passe très fort ?
        $password = $pwdGeneratorAnsi->generate();
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{40}$/', $password);
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::generate().
     */
    public function testGenerate()
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $defaults = $pwdGeneratorAnsi->defaults();

        // Security.salt (CakePHP 2.x)
        $password = $pwdGeneratorAnsi->generate(40, $defaults);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{40}$/', $password);

        // Security.cipherSeed (CakePHP 2.x)
        $params = [
            'lowercase_letters' => false,
            'uppercase_letters' => false,
        ];
        $password = $pwdGeneratorAnsi->generate(29, $params + $defaults);
        $this->assertMatchesRegularExpression('/^[0-9]{29}$/', $password);
    }

    /**
     * Test de la configuration via PasswordGeneratorAnssi::config().
     */
    public function testConfig()
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        // On récupère la configuration par défaut
        $actual = $pwdGeneratorAnsi->config();
        $this->assertEquals($this->defaultConfig, $actual);

        $params = [
            'lowercase_letters' => false,
            'uppercase_letters' => false,
        ];
        $actual = $pwdGeneratorAnsi->config($params);
        $expected = [
            'binary' => false,
            'numbers' => true,
            'lowercase_hexadecimal' => false,
            'uppercase_hexadecimal' => false,
            'lowercase_letters' => false,
            'uppercase_letters' => false,
            'symbols_1' => false,
            'symbols_2' => false,
            'lowercase_accents_fr' => false,
            'uppercase_accents_fr' => false,
        ];
        $this->assertEquals($expected, $actual);

        $actual = $pwdGeneratorAnsi->generate(20);
        $this->assertMatchesRegularExpression('/^[0-9]{20}$/', $actual);

        // Retour aux valeurs par défaut
        $pwdGeneratorAnsi->config($this->defaultConfig);
    }

    /**
     * Retourne une configuration où toutes les classes sont désactivées.
     */
    protected function allDisabled(): array
    {
        return array_fill_keys(array_keys($this->defaultConfig), false);
    }

    /**
     * Retourne une configuration où seules les classes passées en paramètre
     * sont activées.
     */
    protected function only(string ...$classes): array
    {
        return array_fill_keys($classes, true) + $this->allDisabled();
    }

    /**
     * Compte le nombre de caractères d'un mot de passe appartenant à une classe.
     */
    protected function countInClass(string $password, string $class): int
    {
        $characters = mb_str_split($this->classes[$class]);

        return count(array_filter(
            mb_str_split($password),
            fn (string $char) => in_array($char, $characters, true)
        ));
    }

    /**
     * La configuration passée à PasswordGeneratorAnssi::config() se cumule
     * avec la configuration précédente.
     */
    public function testConfigIsCumulative(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $pwdGeneratorAnsi->config(['numbers' => false]);
        $actual = $pwdGeneratorAnsi->config(['symbols_1' => true]);

        $expected = ['numbers' => false, 'symbols_1' => true] + $this->defaultConfig;
        $this->assertEquals($expected, $actual);

        // Sans paramètre, on récupère la configuration en cours
        $this->assertEquals($expected, $pwdGeneratorAnsi->config());
    }

    /**
     * La configuration est propre à chaque instance et ne modifie pas les
     * valeurs par défaut.
     */
    public function testConfigDoesNotAlterDefaults(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();
        $pwdGeneratorAnsi->config($this->allDisabled());

        $this->assertEquals($this->defaultConfig, $pwdGeneratorAnsi->defaults());

        $other = new PasswordGeneratorAnssi();
        $this->assertEquals($this->defaultConfig, $other->config());
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::characters() avec la
     * configuration par défaut.
     */
    public function testCharactersDefaultConfig(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $expected = array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'));
        $this->assertSame($expected, $pwdGeneratorAnsi->characters());
    }

    public function classesProvider(): array
    {
        $result = [];
        foreach (array_keys($this->classes) as $class) {
            $result[$class] = [$class];
        }

        return $result;
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::characters() pour chacune des
     * classes de caractères, y compris les caractères multi-octets.
     *
     * @dataProvider classesProvider
     */
    public function testCharactersByClass(string $class): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $expected = mb_str_split($this->classes[$class]);
        $this->assertSame($expected, $pwdGeneratorAnsi->characters($this->only($class)));
    }

    /**
     * Les caractères communs à plusieurs classes ne sont présents qu'une fois.
     */
    public function testCharactersAreUnique(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $actual = $pwdGeneratorAnsi->characters(
            $this->only('numbers', 'lowercase_hexadecimal', 'uppercase_hexadecimal')
        );
        $expected = array_merge(range('0', '9'), range('a', 'f'), range('A', 'F'));
        $this->assertSame($expected, $actual);

        // 157 caractères au total, dont 34 en double
        $all = array_fill_keys(array_keys($this->defaultConfig), true);
        $actual = $pwdGeneratorAnsi->characters($all);
        $this->assertCount(123, $actual);
        $this->assertSame(array_values(array_unique($actual)), $actual);
    }

    public function testCharactersWithoutClass(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $this->assertSame([], $pwdGeneratorAnsi->characters($this->allDisabled()));
    }

    /**
     * Seule la valeur booléenne true active une classe de caractères.
     */
    public function testCharactersOnlyStrictTrueEnablesClass(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $params = ['binary' => 1, 'symbols_1' => 'yes', 'numbers' => true] + $this->allDisabled();
        $this->assertSame(range('0', '9'), $pwdGeneratorAnsi->characters($params));
    }

    /**
     * Les paramètres de PasswordGeneratorAnssi::characters() priment sur la
     * configuration, sans la modifier.
     */
    public function testCharactersParamsOverrideConfig(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();
        $pwdGeneratorAnsi->config($this->only('numbers'));

        $this->assertSame(range('0', '9'), $pwdGeneratorAnsi->characters());
        $this->assertSame(
            range('a', 'z'),
            $pwdGeneratorAnsi->characters(['numbers' => false, 'lowercase_letters' => true])
        );
        $this->assertEquals($this->only('numbers'), $pwdGeneratorAnsi->config());
    }

    public function testCharactersWithEncoding(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $params = $this->only('lowercase_accents_fr');
        $expected = mb_str_split($this->classes['lowercase_accents_fr']);
        $this->assertSame($expected, $pwdGeneratorAnsi->characters($params, 'UTF-8'));
    }

    public function lengthProvider(): array
    {
        return [
            'empty' => [0],
            'one' => [1],
            'twelve' => [12],
            'long' => [256],
        ];
    }

    /**
     * Test de la longueur des mots de passe générés par
     * PasswordGeneratorAnssi::generate().
     *
     * @dataProvider lengthProvider
     */
    public function testGenerateLength(int $length): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $password = $pwdGeneratorAnsi->generate($length);
        $this->assertMatchesRegularExpression(sprintf('/^[a-zA-Z0-9]{%d}$/', $length), $password);
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::generate() pour chacune des
     * classes de caractères : la longueur est comptée en caractères et non en
     * octets.
     *
     * @dataProvider classesProvider
     */
    public function testGenerateByClass(string $class): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $password = $pwdGeneratorAnsi->generate(64, $this->only($class));
        $this->assertSame(64, mb_strlen($password));
        $this->assertSame(64, $this->countInClass($password, $class));
    }

    public function testGenerateWithEncoding(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $password = $pwdGeneratorAnsi->generate(20, $this->only('uppercase_accents_fr'), 'UTF-8');
        $this->assertSame(20, mb_strlen($password));
        $this->assertSame(20, $this->countInClass($password, 'uppercase_accents_fr'));
    }

    /**
     * Les paramètres de PasswordGeneratorAnssi::generate() ne modifient pas la
     * configuration.
     */
    public function testGenerateDoesNotAlterConfig(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $pwdGeneratorAnsi->generate(10, $this->only('binary'));
        $this->assertEquals($this->defaultConfig, $pwdGeneratorAnsi->config());
    }

    /**
     * Les mots de passe générés sont aléatoires.
     */
    public function testGenerateIsRandom(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        // Probabilité de collision : 62^-40
        $this->assertNotSame($pwdGeneratorAnsi->generate(), $pwdGeneratorAnsi->generate());

        // Probabilité qu'un des deux caractères manque : 2^-127
        $password = $pwdGeneratorAnsi->generate(128, $this->only('binary'));
        $this->assertStringContainsString('0', $password);
        $this->assertStringContainsString('1', $password);
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::constrained() avec les valeurs
     * par défaut.
     */
    public function testConstrainedDefault(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{40}$/', $pwdGeneratorAnsi->constrained());
    }

    public function testConstrainedEmpty(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $this->assertSame('', $pwdGeneratorAnsi->constrained(0));
    }

    /**
     * Le nombre de caractères obligatoires de chaque classe est respecté, même
     * pour les caractères multi-octets, et le nombre de caractères
     * obligatoires peut être égal à la longueur.
     */
    public function testConstrainedExactMandatoryCount(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $mandatory = [
            'lowercase_accents_fr' => 3,
            'symbols_1' => 2,
            'uppercase_accents_fr' => 1,
        ];
        $password = $pwdGeneratorAnsi->constrained(6, [], $mandatory, 'UTF-8');

        $this->assertSame(6, mb_strlen($password));
        foreach ($mandatory as $class => $number) {
            $this->assertSame($number, $this->countInClass($password, $class), $class);
        }
    }

    /**
     * Les classes obligatoires sont utilisées même si elles ne font pas partie
     * des paramètres ; le reste du mot de passe est complété avec les classes
     * des paramètres.
     */
    public function testConstrainedMandatoryOutsideParams(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $password = $pwdGeneratorAnsi->constrained(10, $this->only('numbers'), ['uppercase_letters' => 2]);

        $this->assertSame(10, mb_strlen($password));
        $this->assertSame(2, $this->countInClass($password, 'uppercase_letters'));
        $this->assertSame(8, $this->countInClass($password, 'numbers'));
    }

    public function testConstrainedWithZeroMandatory(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $password = $pwdGeneratorAnsi->constrained(12, $this->only('numbers'), ['symbols_2' => 0]);
        $this->assertMatchesRegularExpression('/^[0-9]{12}$/', $password);
    }

    /**
     * Les caractères obligatoires ne sont pas toujours placés au début du mot
     * de passe.
     */
    public function testConstrainedIsShuffled(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        // Probabilité que l'une des deux positions ne soit jamais vue : 2^-63
        $positions = [];
        for ($idx = 0; $idx < 64; ++$idx) {
            $password = $pwdGeneratorAnsi->constrained(2, $this->only('numbers'), ['uppercase_letters' => 1]);
            $positions[strcspn($password, implode('', range('A', 'Z')))] = true;
        }
        ksort($positions);

        $this->assertSame([0 => true, 1 => true], $positions);
    }

    /**
     * Les paramètres de PasswordGeneratorAnssi::constrained() ne modifient pas
     * la configuration.
     */
    public function testConstrainedDoesNotAlterConfig(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $pwdGeneratorAnsi->constrained(10, $this->only('binary'), ['numbers' => 1]);
        $this->assertEquals($this->defaultConfig, $pwdGeneratorAnsi->config());
    }

    /**
     * Le message d'exception liste les classes obligatoires.
     */
    public function testConstrainedExceptionMessageListsMandatory(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        try {
            $pwdGeneratorAnsi->constrained(2, [], ['numbers' => 2, 'symbols_1' => 1]);
            $this->fail('Une PasswordException aurait dû être levée');
        } catch (PasswordException $exception) {
            $this->assertSame(
                'Impossible de générer un mot de passe de longueur 2 avec 3 caractères obligatoires '.
                '(obligatoires: {"numbers":2,"symbols_1":1})',
                $exception->getMessage()
            );
        }
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::characters() avec toutes les
     * classes activées et un encodage explicite.
     */
    public function testCharactersAllClassesWithEncoding(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $all = array_fill_keys(array_keys($this->defaultConfig), true);
        $this->assertSame($pwdGeneratorAnsi->characters($all), $pwdGeneratorAnsi->characters($all, 'UTF-8'));
    }

    public function testGenerateEmptyWithEncoding(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $this->assertSame('', $pwdGeneratorAnsi->generate(0, [], 'UTF-8'));
    }

    public function constrainedProvider(): array
    {
        return [
            'sans obligatoire, longueur 0, encodage' => [0, [], 'UTF-8', '/^$/'],
            'sans obligatoire, encodage' => [3, [], 'UTF-8', '/^[a-zA-Z0-9]{3}$/'],
            'obligatoire à 0, longueur 0' => [0, ['numbers' => 0], null, '/^$/'],
            'obligatoire à 0, longueur 0, encodage' => [0, ['numbers' => 0], 'UTF-8', '/^$/'],
            'obligatoire à 0, encodage' => [3, ['numbers' => 0], 'UTF-8', '/^[a-zA-Z0-9]{3}$/'],
            'obligatoires égaux à la longueur' => [2, ['numbers' => 2], null, '/^[0-9]{2}$/'],
            'obligatoires et complément, encodage' => [3, ['numbers' => 1], 'UTF-8', '/^(?=.*[0-9])[a-zA-Z0-9]{3}$/'],
        ];
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::constrained() selon la
     * longueur, les classes obligatoires et l'encodage.
     *
     * @dataProvider constrainedProvider
     */
    public function testConstrainedCombinations(int $length, array $mandatory, ?string $encoding, string $regexp): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $password = $pwdGeneratorAnsi->constrained($length, [], $mandatory, $encoding);
        $this->assertMatchesRegularExpression($regexp, $password);
    }

    public function constrainedExceptionProvider(): array
    {
        return [
            'sans caractère disponible, encodage' => [
                3,
                $this->allDisabled(),
                [],
                'UTF-8',
                'Impossible de générer un mot de passe de longueur 3 sans caractère disponible',
            ],
            'trop de caractères obligatoires, encodage' => [
                1,
                [],
                ['numbers' => 1, 'lowercase_letters' => 1],
                'UTF-8',
                'Impossible de générer un mot de passe de longueur 1 avec 2 caractères obligatoires',
            ],
            'classe obligatoire inconnue seule' => [
                3,
                [],
                ['not_existing' => 1],
                null,
                "La classe de caractère not_existing n'est pas défini",
            ],
            'classe obligatoire inconnue seule, encodage' => [
                3,
                [],
                ['not_existing' => 1],
                'UTF-8',
                "La classe de caractère not_existing n'est pas défini",
            ],
        ];
    }

    /**
     * Test des exceptions de PasswordGeneratorAnssi::constrained() avec et
     * sans encodage explicite.
     *
     * @dataProvider constrainedExceptionProvider
     */
    public function testConstrainedExceptions(
        int $length,
        array $params,
        array $mandatory,
        ?string $encoding,
        string $message,
    ): void {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $this->expectException(PasswordException::class);
        $this->expectExceptionMessage($message);
        $pwdGeneratorAnsi->constrained($length, $params, $mandatory, $encoding);
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::constrained().
     */
    public function testConstrained()
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $defaults = $pwdGeneratorAnsi->defaults();
        $mandatory = [
            'numbers' => 1,
            'lowercase_letters' => 1,
            'uppercase_letters' => 1,
            'symbols_1' => 1,
        ];

        // Entropie 37, force 1
        $password = $pwdGeneratorAnsi->constrained(6, $defaults, $mandatory);

        $this->assertNotFalse(mb_ereg('^([a-zA-Z0-9]|[€\\!#\\$\\*%\\? ]){6}$', $password));
        $this->assertNotFalse(mb_ereg('[a-z]', $password));
        $this->assertNotFalse(mb_ereg('[A-Z]', $password));
        $this->assertNotFalse(mb_ereg('[0-9]', $password));
        $this->assertNotFalse(mb_ereg('[€\\!#\\$\\*%\\? ]', $password));
    }

    public function testConstrainedException1()
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $defaults = $pwdGeneratorAnsi->defaults();
        $mandatory = [
            'numbers' => 1,
            'lowercase_letters' => 1,
        ];
        $this->expectException(PasswordException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Impossible de générer un mot de passe de longueur 1 avec 2 caractères obligatoires'
        );
        $pwdGeneratorAnsi->constrained(1, $defaults, $mandatory);
    }

    public function testConstrainedException2()
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $defaults = $pwdGeneratorAnsi->defaults();
        $false = array_combine(array_keys($defaults), array_fill(0, count($defaults), false));
        $mandatory = [
            'numbers' => 1,
            'lowercase_letters' => 1,
        ];
        $this->expectException(PasswordException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Impossible de générer un mot de passe de longueur 3 sans caractère disponible'
        );
        $pwdGeneratorAnsi->constrained(3, $false, $mandatory);
    }

    public function testWithUnexpectedClass()
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $mandatory = [
            'numbers' => 1,
            'not_existing' => 1,
        ];
        $this->expectException(PasswordException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            "La classe de caractère not_existing n'est pas défini"
        );
        $pwdGeneratorAnsi->constrained(3, $mandatory, $mandatory);
    }

    public function testGenerateWithoutAvailableCharacter()
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $defaults = $pwdGeneratorAnsi->defaults();
        $false = array_combine(array_keys($defaults), array_fill(0, count($defaults), false));
        $this->expectException(PasswordException::class);
        $this->expectExceptionMessage(
            'Impossible de générer un mot de passe de longueur 12 sans caractère disponible'
        );
        $pwdGeneratorAnsi->generate(12, $false);
    }
}
