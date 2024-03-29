# M2-System-Merge

The system is used to connect two game servers into one. Moves items, account, player without overwriting the ID and not losing items.   Everything is correctly assigned.  
Example:  
We have the first server with 1000 account, 2000 player, 10,000 items.  
We have a second server with 500 account, 1000 player and 3000 items.  
The system combines everything in:  
1500 account, 3000 player, 13000 items without losing anything. All IDs are correctly assigned, all items are correctly assigned to new users.  
The system is fully automatic, you can easily add new tables and columns. (It's a generator)  

I recommend PHP version min. 7.4

USE THIS AS A CLI EXAMPLE: php cli.php  
Remember to set the character encoding  
May two databases have to have the same file structures  
keep account, player and other tables in one database  
Make a copy of the data, do not operate live. It's best to create two new bases and upload two servers there. The script says "secondBase" so be careful. MAKE A BACKUP COPY  
Remember to increase the maximum number of characters in the tables player.name account.login guild.name and wherever there are names.
Use characters that are not available in the game, e.g. "-", "_", "+"
 
Support for
 ```
 affect
 guild
 item
 player
 player_index
 player_gift (option system)
 player_shop_items (option system)
 quest
 safebox
 guild_grade
 guild_member
 marriage
 messenger_list
 ```

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

// Migration Process
$migration->start();
 ```
 
 The system does have a positive side effect. Cleans inanimate objects. (If the user deletes the character, the game engine does not delete these items)
