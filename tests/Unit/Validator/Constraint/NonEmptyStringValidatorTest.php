<?php declare(strict_types=1);

use MichaelCozzolino\SymfonyValidationEnhancementsBundle\Enum\MySqlDatabaseStringLength;
use MichaelCozzolino\SymfonyValidationEnhancementsBundle\Validator\Constraint\NonEmptyString;
use MichaelCozzolino\SymfonyValidationEnhancementsBundle\Validator\Constraint\NonEmptyStringValidator;
use MichaelCozzolino\SymfonyValidationEnhancementsBundle\Validator\Constraint\NonEmptyText;
use MichaelCozzolino\SymfonyValidationEnhancementsBundle\Validator\Constraint\NonEmptyVarcharDefault;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

covers(NonEmptyStringValidator::class);

dataset('unexpected types', function () {
    return [
        [2, new NonEmptyString()],
        ['hi', new Email()],
    ];
});

test('validate when an unexpected type exception occurs', function (string | int $value, NonEmptyString | Email $constraint) {
    $this->validator->validate($value, $constraint);
})->with('unexpected types')
  ->throws(UnexpectedTypeException::class);

dataset('non empty strings', function () {
    return [
        [null, 'hello'],
        [null, null],
        [10, 'value'],
        [100, '          value with spaces    '],
        [4, '     four     ',],
    ];
});

test('non empty string is valid', function (?int $max, ?string $value) {
    $constraint = new NonEmptyString($max);

    $this->validator->validate($value, $constraint);

    $this->assertNoViolation();
})->with('non empty strings');

dataset('non empty string is not valid', function () {
    return [
        [50, '          ',],
        [502, '',],
        [2, 'abc',],
    ];
});

test('non empty string is not valid', function (?int $max, string $value) {
    $constraint = new NonEmptyString($max);

    $this->validator->validate($value, $constraint);

    $this->assertViolations(1);
})->with('non empty string is not valid');

test('non empty varchar default is valid', function () {
    $this->validator->validate(
        $this->generateRandomString(MySqlDatabaseStringLength::VarcharDefault->value),
        new NonEmptyVarcharDefault()
    );

    $this->assertNoViolation();
});

test('non empty text is valid', function () {
    $this->validator->validate(
        $this->generateRandomString(MySqlDatabaseStringLength::Text->value),
        new NonEmptyText()
    );

    $this->assertNoViolation();
});

test('non empty varchar default is not valid', function () {
    $this->validator->validate(
        $this->generateRandomString(1000 + MySqlDatabaseStringLength::VarcharDefault->value),
        new NonEmptyVarcharDefault()
    );

    $this->assertViolations(1);
});

test('non empty text is not valid', function () {
    $this->validator->validate(
        $this->generateRandomString(MySqlDatabaseStringLength::Text->value + 9382),
        new NonEmptyText()
    );

    $this->assertViolations(1);
});