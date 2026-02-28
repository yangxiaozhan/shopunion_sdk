# ShopUnion SDK 调试工具

用于调试淘宝联盟、多多进宝、京东联盟各接口的 HTML 工具。

## 使用方式

1. **配置凭证**（二选一）：
   - 复制 `config.sample.php` 为 `config.local.php`，填写各平台 app_key、app_secret 等
   - 或设置环境变量（见 config.sample.php 注释）

2. **启动本地 PHP 服务**：
   ```bash
   cd tools && php -S localhost:8760
   ```

3. **打开浏览器**：
   访问 http://localhost:8760

4. 选择平台和接口，填写参数，点击「发起请求」即可查看返回结果。

## 接口说明

| 平台 | 物料搜索 | 链接转换 | 店铺搜索 | 商品详情 | 商品列表 | 生成淘口令 | 物料分类列表 | 物料精选商品列表 |
|------|----------|----------|----------|----------|----------|------------|--------------|------------------|
| 淘宝 | keyword, page_no, page_size, material_id | url / urls（长链转短链） | keyword, page_no, page_size | num_iids/item_id(最多20) | page_num, page_size, promotion_id(默认62191) | url（推广链接） | subject(默认1), material_type(默认1) | material_id(来自分类列表), page_no, page_size |
| 拼多多 | keyword, page, page_size | goods_sign_list / goods_id_list | keyword, page, page_size | goods_sign_list / goods_id_list | — | — | — | — |
| 京东 | keyword, page_index, page_size | material_id | 同物料搜索 | sku_ids | — | — | — | — |

高级选项中可临时覆盖配置（测试不同账号时使用）。
