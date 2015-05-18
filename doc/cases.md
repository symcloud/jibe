+---+----------------+----------------+---------------------------------------------+-------------------------------------------------------------+
| # | Client         | Server         | Description                                 | Action                                                      |
+---+------+---------+------+---------+---------------------------------------------+----------+--------+--------------+---------------+----------+
|   | hash | version | hash | version |                                             | Download | Upload | Delete local | Delete server | Conflict |
+---+------+---------+------+---------+---------------------------------------------+----------+--------+--------------+---------------+----------+
| 1 | X    | 1       | X    | 1       | Nothing to be done                          |          |        |              |               |          |
| 2 | X    | 1       | Y    | 2       | Server file changed, download new version   | x        |        |              |               |          |
| 3 | Y    | 1       | X    | 1       | Client file change, upload new version      |          | x      |              |               |          |
| 4 | Y    | 1       | Z    | 2       | Client and Server file changed, conflict    |          |        |              |               | x        |
| 5 | Y    | 1       | Y    | 2       | Server file changed but content is the same |          |        |              |               |          |
| 6 | X    | -       | -    | -       | New client file, upload it                  |          | x      |              |               |          |
| 7 | -    | -       | X    | 1       | New server file, download it                | x        |        |              |               |          |
| 8 | X    | 1       | -    | -       | Server file deleted, remove client version  |          |        | x            |               |          |
| 9 | -    | 1       | X    | 1       | Client file deleted, remove server version  |          |        |              | x             |          |
+---+------+---------+------+---------+---------------------------------------------+----------+--------+--------------+---------------+----------+

| 1 | c.hash == s.hash    | c.version == s.version | -
| 2 | c.hash != s.hash    | c.version <  s.version | x
| 3 | c.hash != s.hash    | c.version == s.version | x 
| 4 | c.hash != s.hash    | c.version <  s.version | x
|   | c.oldHash != s.hash |                        | x
| 5 | c.hash == s.hash    | c.version != s.version | -
| 6 |                     |                        | x  
| 7 |                     |                        | x
| 8 |                     |                        | x 
| 9 |                     |                        | x
