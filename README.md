<p align="center"><img src="docs/logotype.png" alt="Phrity Net Stream" width="100%"></p>

[![Build Status](https://github.com/sirn-se/phrity-net-stream/actions/workflows/acceptance.yml/badge.svg)](https://github.com/sirn-se/phrity-net-stream/actions)
[![Coverage Status](https://coveralls.io/repos/github/sirn-se/phrity-net-stream/badge.svg?branch=main)](https://coveralls.io/github/sirn-se/phrity-net-stream?branch=main)

# Introduction

Package that provide implementations of [PSR-7 StreamInterface](https://www.php-fig.org/psr/psr-7/#34-psrhttpmessagestreaminterface)
and [PSR-17 StreamFactoryInterface](https://www.php-fig.org/psr/psr-17/#24-streamfactoryinterface)
but also adds stream and socket related funcitonality.
It is designed for use with socket connections.

## Installation

Install with [Composer](https://getcomposer.org/);
```
composer require phrity/net-stream
```

## Included classes

* [Stream](docs/Stream.md) - PSR-7 StreamFactory compatible Stream class
* [SocketStream](docs/SocketStream.md) - Extending Stream for additional methods
* [SocketClient](docs/SocketClient.md) - Socket Client that creates SocketStream connections
* [SocketServer](docs/SocketServer.md) - Socket Server that creates SocketStream connections
* [StreamCollection](docs/StreamCollection.md) - Collection of connections
* [StreamFactory](docs/StreamFactory.md) - PSR-17 StreamFactoryInterface compatible factory
* [StreamException](docs/StreamException.md) - Exception for stream related errors
* [Context](docs/Context.md) - Context wrapper fro streams


## Versions

| Version | PHP | |
| --- | --- | --- |
| `2.3` | `^8.1` | Float timeout, hasContents method, Listeners |
| `2.2` | `^8.1` | Improved context handling |
| `2.1` | `^8.0` | Set context on server |
| `2.0` | `^8.0` | Modernization |
| `1.3` | `^7.4\|^8.0` | Closing read and write separately |
| `1.2` | `^7.4\|^8.0` | Socket client |
| `1.1` | `^7.4\|^8.0` | Stream collection |
| `1.0` | `^7.4\|^8.0` | Initial version |
