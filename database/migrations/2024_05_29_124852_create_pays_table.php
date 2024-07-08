    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::create('pays', function (Blueprint $table) {
                $table->uuid('id');
                $table->uuid('target_id')->constrained()->onDelete('cascade')->index();
                $table->bigInteger('uang_masuk');
                $table->enum('operasi',['tambah', 'kurang']);
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('pays');
        }
    };
