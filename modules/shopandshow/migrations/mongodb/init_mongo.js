
use admin
db.createUser(
    {
        user: "ss_user",
        pwd: "abc123",
        roles: [ { role: "userAdminAnyDatabase", db: "admin" } ]
    }
);

use admin
db.categories.createIndex( { "id": 1 }, { unique: true } );
db.products.createIndex( { "id": 1 }, { unique: true } );
db.shares.createIndex( { "id": 1 }, { unique: true } );
db.products_variations.createIndex( { "id": 1 }, { unique: true } );
db.products_variations.createIndex( { "product_id": 1 }, { unique: false } );