<?php

Route::group(
    [
        'prefix' => 'usora',
    ],
    function () {
        // user json
        Route::get(
            'user/index',
            \Railroad\Usora\Controllers\UserJsonController::class . '@index'
        )
            ->name('usora.api.user.index');

        Route::get(
            'user/show/{id}',
            \Railroad\Usora\Controllers\UserJsonController::class . '@show'
        )
            ->name('usora.api.user.show');

        Route::put(
            'user/store',
            \Railroad\Usora\Controllers\UserJsonController::class . '@store'
        )
            ->name('usora.api.user.store');

        Route::patch(
            'user/update/{id}',
            \Railroad\Usora\Controllers\UserJsonController::class . '@update'
        )
            ->name('usora.api.user.update');

        Route::delete(
            'user/delete/{id}',
            \Railroad\Usora\Controllers\UserJsonController::class . '@delete'
        )
            ->name('usora.api.user.delete');

        // user form
        Route::put(
            'user/store',
            \Railroad\Usora\Controllers\UserController::class . '@store'
        )
            ->name('usora.user.store');

        Route::patch(
            'user/update/{id}',
            \Railroad\Usora\Controllers\UserController::class . '@update'
        )
            ->name('usora.user.update');
        Route::delete(
            'user/delete/{id}',
            \Railroad\Usora\Controllers\UserController::class . '@delete'
        )
            ->name('usora.user.delete');

        // user field json
        Route::get(
            'user-field/index/{id}',
            \Railroad\Usora\Controllers\UserFieldJsonController::class . '@index'
        )
            ->name('usora.api.user-field.index');

        Route::get(
            'user-field/show/{id}',
            \Railroad\Usora\Controllers\UserFieldJsonController::class . '@show'
        )
            ->name('usora.api.user-field.show');

        Route::put(
            'user-field/store',
            \Railroad\Usora\Controllers\UserFieldJsonController::class . '@store'
        )
            ->name('usora.api.user-field.store');

        Route::patch(
            'user-field/update/{id}',
            \Railroad\Usora\Controllers\UserFieldJsonController::class . '@update'
        )
            ->name('usora.api.user-field.update');

        Route::patch(
            'user-field/update-or-create-by-key',
            \Railroad\Usora\Controllers\UserFieldJsonController::class . '@updateOrCreateByKey'
        )
            ->name('usora.api.user-field.update-or-create-by-key');

        Route::patch(
            'user-field/update-or-create-multiple-by-key',
            \Railroad\Usora\Controllers\UserFieldJsonController::class . '@updateOrCreateMultipleByKey'
        )
            ->name('usora.api.user-field.update-or-create-multiple-by-key');

        Route::delete(
            'user-field/delete/{id}',
            \Railroad\Usora\Controllers\UserFieldJsonController::class . '@delete'
        )
            ->name('usora.api.user-field.delete');

        // user field form
        Route::put(
            'user-field/store',
            \Railroad\Usora\Controllers\UserFieldController::class . '@store'
        )
            ->name('usora.user-field.store');

        Route::patch(
            'user-field/update/{id}',
            \Railroad\Usora\Controllers\UserFieldController::class . '@update'
        )
            ->name('usora.user-field.update');

        Route::patch(
            'user-field/update-or-create-by-key',
            \Railroad\Usora\Controllers\UserFieldController::class . '@updateOrCreateByKey'
        )
            ->name('usora.user-field.update-or-create-by-key');

        Route::patch(
            'user-field/update-or-create-multiple-by-key',
            \Railroad\Usora\Controllers\UserFieldController::class . '@updateOrCreateMultipleByKey'
        )
            ->name('usora.user-field.update-or-create-multiple-by-key');

        Route::delete(
            'user-field/delete/{id}',
            \Railroad\Usora\Controllers\UserFieldController::class . '@delete'
        )
            ->name('usora.user-field.delete');

    }
);
