# M2-System-Merge

I recommend PHP version min. 7.2

USE THIS AS A CLI EXAMPLE: php cli.php  
Remember to set the character encoding  
May two databases have to have the same file structures  
keep account, player and other tables in one database  
Make a copy of the data, do not operate live. It's best to create two new bases and upload two servers there. The script says "secondBase" so be careful. MAKE A BACKUP COPY  
 
 Example:
 ```
 $migration = new Migration;

// Coding Type
$migration->charset = 'gb2312_chinese_ci';

// Table settings
$migration->account('account', 'id', ['login', 'password', 'social_id', 'email', 'securitycode', 'status', 'availDt', 'create_time', 'last_play', 'gold_expire', 'silver_expire', 'safebox_expire', 'autoloot_expire', 'fish_mind_expire', 'marriage_fast_expire', 'money_drop_rate_expire', 'real_name', 'question1', 'answer1', 'question2', 'answer2', 'cash']);
$migration->player('player', 'id', 'account', ['name', 'job', 'kingdom', 'voice', 'dir', 'x', 'y', 'z', 'map_index', 'exit_x', 'exit_y', 'exit_map_index', 'hp', 'mp', 'stamina', 'random_hp', 'random_sp', 'playtime', 'level', 'level_step', 'st', 'ht', 'dx', 'iq', 'exp', 'gold', 'stat_point', 'skill_point', 'quickslot', 'ip', 'part_main', 'part_base', 'part_hair', 'part_acce', 'skill_group', 'skill_level', 'alignment', 'last_play', 'change_name', 'mobile', 'sub_skill_point', 'stat_reset_count', 'horse_hp', 'horse_stamina', 'horse_level', 'horse_hp_droptime', 'horse_riding', 'horse_skill_point']);

// Database connection settings
$migration->firstBase('localhost', 'user', 'pass', 'migration_1');
$migration->secondBase('localhost', 'user', 'pass', 'migration_2');

// List of tables to migrate
// The account and player table is automatically added
$migration->add('item', 'owner_id', ['window', 'pos', 'count', 'vnum', 'socket0', 'attrtype0', 'attrvalue0', 'attrtype1', 'attrvalue1', 'attrtype2', 'attrvalue2', 'attrtype3', 'attrvalue3', 'attrtype4', 'attrvalue4', 'attrtype5', 'attrvalue5', 'attrtype6', 'attrvalue6']);
$migration->add('guild', 'master', ['name', 'sp', 'level', 'exp', 'skill_point', 'skill', 'win', 'draw', 'loss', 'ladder_point', 'gold']);
$migration->add('quest', 'dwPID', ['szName', 'szState', 'lValue']);

// Migration Process
$migration->start();
 ```
 
 ```
 $migration->add('table name', 'player index', ['array column table']);
 ```
