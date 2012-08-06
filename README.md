# FuelPHP SimplerAuth Package

Simpler than SimpleAuth. No database, no role. Simple but secure a bit.

## Installing

Simply add `simplerauth` to your config.php `always_loaded.packages` config option.

## Usage

### How to create users

```
$ oil console
>>> Auth::create_user('username', 'password');
```

Output:
```
'username' => array(
	'a32ca7aa311e7e6dcadc208303aa1562',
	'8e072d2df13769a6a88cd0a20ca3d789a50c780147c03abd4b1443431729d7bc'
),
```

Copy config/simplerauth.php to app/config folder, and paste the output into the file.

### Methods

Auth::login()

Auth::logout()

Auth::check()

Auth::get_username()

## License

MIT License. See LICENSE.
