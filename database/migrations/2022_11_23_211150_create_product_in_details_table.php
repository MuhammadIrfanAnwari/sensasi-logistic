<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProductInDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql')->create('product_in_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_in_id')
                ->constrained('product_ins')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->integer('qty');
            $table->unique(['product_id', 'product_in_id']);
        });


        DB::connection('mysql')->unprepared('CREATE OR REPLACE PROCEDURE product_monthly_movements_upsert_in_procedure (
                IN productID int,
                IN yearAt int,
                IN monthAt int
            )
            BEGIN
                INSERT INTO
                    product_monthly_movements (product_id, year, month, `in`)
                SELECT
                    product_id,
                    yearAt,
                    monthAt,
                    @total_qty := SUM(qty)
                FROM (SELECT productID as product_id, pid.qty
                    FROM product_in_details AS pid
                    LEFT JOIN product_ins AS `pi` ON pid.product_in_id = `pi`.id
                    WHERE
                        pid.product_id = productID AND
                        `pi`.deleted_at IS NULL AND
                        YEAR(`pi`.at) = yearAt AND
                        MONTH(`pi`.at) = monthAt
                UNION SELECT productID, 0
                ) AS qty_temp
                GROUP BY product_id
                ON DUPLICATE KEY UPDATE `in` = @total_qty;
            END;
        ');


        DB::connection('mysql')->unprepared('CREATE OR REPLACE PROCEDURE product_in_details__product_monthly_movements_procedure (
                IN productInID int,
                IN productID int
            )
            BEGIN
                DECLARE yearAt int;
                DECLARE monthAt int;

                SELECT YEAR(`at`), MONTH(`at`) INTO yearAt, monthAt
                FROM product_ins
                WHERE id = productInID;

                CALL product_monthly_movements_upsert_in_procedure(productID, yearAt, monthAt);
            END;
        ');

        DB::connection('mysql')->unprepared('CREATE OR REPLACE TRIGGER product_ins_after_update_trigger
                AFTER UPDATE
                ON product_ins
                FOR EACH ROW
            BEGIN
                -- TODO: optimize this IF
                IF (OLD.deleted_at IS NULL AND NEW.deleted_at IS NOT NULL) OR (NEW.deleted_at IS NULL AND OLD.deleted_at IS NOT NULL) THEN
                    CALL product_in_details__product_monthly_movements_procedure(
                        OLD.id,
                        (
                            SELECT product_id
                            FROM product_in_details
                            WHERE product_in_id = OLD.id
                        )
                    );
                END IF;

                IF YEAR(NEW.at) <> YEAR(OLD.at) OR MONTH(NEW.at) <> MONTH(OLD.at) THEN
                    CALL product_monthly_movements_upsert_in_procedure(
                        (SELECT product_id FROM product_in_details WHERE product_in_id = OLD.id),
                        YEAR(OLD.at),
                        MONTH(OLD.at)
                    );

                    CALL product_in_details__product_monthly_movements_procedure(
                        OLD.id,
                        (SELECT product_id FROM product_in_details WHERE product_in_id = OLD.id)
                    );
                END IF;
            END;
        ');

        DB::connection('mysql')->unprepared('CREATE OR REPLACE TRIGGER product_in_details_after_insert_trigger
                AFTER INSERT
                ON product_in_details
                FOR EACH ROW
            BEGIN
                CALL product_in_details__product_monthly_movements_procedure(NEW.product_in_id, NEW.product_id);
            END;
        ');

        DB::connection('mysql')->unprepared('CREATE OR REPLACE TRIGGER product_in_details_after_update_trigger
                AFTER UPDATE
                ON product_in_details
                FOR EACH ROW
            BEGIN
                IF NEW.qty <> OLD.qty AND NEW.product_id = OLD.product_id THEN
                    CALL product_in_details__product_monthly_movements_procedure(NEW.product_in_id, NEW.product_id);
                END IF;

                IF NEW.product_id <> OLD.product_id THEN
                    CALL product_in_details__product_monthly_movements_procedure(NEW.product_in_id, OLD.product_id);
                    CALL product_in_details__product_monthly_movements_procedure(NEW.product_in_id, NEW.product_id);
                END IF;
            END;
            ');

        DB::connection('mysql')->unprepared('CREATE OR REPLACE TRIGGER product_in_details_after_delete_trigger
                AFTER DELETE
                ON product_in_details
                FOR EACH ROW
            BEGIN
                CALL product_in_details__product_monthly_movements_procedure(OLD.product_in_id, OLD.product_id);
            END;
        ');
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql')->dropIfExists('product_in_details');
        DB::connection('mysql')->unprepared('DROP PROCEDURE IF EXISTS `product_monthly_movements_upsert_in_procedure`');
        DB::connection('mysql')->unprepared('DROP PROCEDURE IF EXISTS `product_in_details__product_monthly_movements_procedure`');
        DB::connection('mysql')->unprepared('DROP TRIGGER IF EXISTS product_ins_after_update_trigger');
        DB::connection('mysql')->unprepared('DROP TRIGGER IF EXISTS product_in_details_after_insert_trigger');
        DB::connection('mysql')->unprepared('DROP TRIGGER IF EXISTS product_in_details_after_update_trigger');
        DB::connection('mysql')->unprepared('DROP TRIGGER IF EXISTS product_in_details_after_delete_trigger');
    }
}
