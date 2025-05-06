运用
jimi
├─ README.md 
├─ app 
│  ├─ Controller 
│  ├─ Jimi.php 
│  ├─ Models 
│  │  └─ User.php 
│  ├─ System 
│  │  ├─ Autoloading 
│  │  │  └─ Autoloading.php 
│  │  ├─ Config.php 
│  │  ├─ Define.php 
│  │  ├─ HTTP 
│  │  │  ├─ Request.php 
│  │  │  └─ Response.php 
│  │  └─ Helpers.php 
│  └─ Views 
├─ cache 
├─ config 
│  └─ system.php 
├─ index.php 
└─ t.php 

API-First
Autoloading
ORM  - CRUD (创建、读取、更新、删除) 应用

-----
### 方法使用

 **头部引入**
 
`use App\System\Helpers;`

 **使用方式**
 
 `Helpers::方法名称();`
 -----
 ### 常量使用

 **头部引入**
 
`use App\System\Define;`

 **使用方式**
 
 `echo Define::get('SYS_TIME');`
 
  ### 配置使用

 **头部引入**
 
`use App\System\Config;`

 **使用方式**
 
 `echo Config::get('web_path');`
 
 *扩展使用方式*
  - 支持不同配置文件(默认’system‘)
  `Config::load('Router');`
  
  - 支持多层级的配置项，例如 database.db_host
 `echo Config::get('web_path.db_host');`

 
 [Routers-路由-说明](/docs/Router.md)