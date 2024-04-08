# Changelog

## 1.12.0

- Support for Symfony 7

# 1.11.0

- Using annotations for routes

# 1.10.0

- Allow different Redis classes to be used

# 1.9.0

- Support for Symfony 6

# 1.8.0

- Dependency updates

## 1.7.0

- Fix default redis port
  
  This is potentially breaking change, but given its very unlikely someone is
  using this wrong default port, I'm going to make it a minor fix.

## 1.6.1

- Fix travis tests

## 1.6.0

- Enable Symfony 5

## 1.5.0

- Change to XML routes file.

## 1.4.0

- Symfony 4.1 deprecations.

## 1.3.0

- Remove dependency on framework-extra-bundle.

## 1.2.1

- Catch all Redis errors.

## 1.2.0

- Inject services into the controller instead of using `container->get`.
- Add redis factory helper class.
- Support for Symfony 4.
