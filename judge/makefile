all: src/dispatcher.c src/judge.c
	gcc -std=c99 -Wall -c -I/usr/local/mysql/include/mysql -I/usr/include/mysql  src/dispatcher.c
	gcc -std=c99 -Wall -o dispatcher dispatcher.o -L/usr/local/mysql/lib/mysql -L/usr/lib/mysql  -L/usr/lib64/mysql  -lmysqlclient

	gcc -std=c99 -Wall -c -I/usr/local/mysql/include/mysql -I/usr/include/mysql src/judge.c
	gcc -std=c99 -Wall -o judge judge.o -L/usr/local/mysql/lib/mysql -L/usr/lib/mysql  -L/usr/lib64/mysql -lmysqlclient
	rm judge.o dispatcher.o