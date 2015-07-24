# swoole-linux-tools
运用swoole友好的实现Linux性能监控工具集合（free,uptime等）

![Linux free screenshot](https://raw.githubusercontent.com/smalleyes/swoole-linux-dash/master/linux-tools/free/doc/free.png)
![Linux uptime screenshot](https://raw.githubusercontent.com/smalleyes/swoole-linux-dash/master/linux-tools/uptime/doc/uptime.png)

## 依赖

* PHP 5.3+
* Swoole 1.7.16
* Linux, OS X and basic Windows support (Thanks to cygwin)

## 安装 Swoole扩展

1. Install from pecl
    
    ```
    pecl install swoole
    ```

2. Install from source

    ```
    sudo apt-get install php5-dev
    git clone https://github.com/swoole/swoole-src.git
    cd swoole-src
    phpize
    ./configure
    make && make install
    ```
