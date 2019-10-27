<?php

use Dev\Infrastructure\Models\CustomerModels\CustomerModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * PermissionsTableSeeder Class responsible for permissions seeding
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permissions')->insert([
            [
                'topic' => "City",
                'action' => "display-edit-city-form",
                "description" => "Display city edit form."
            ],
            [
                'topic' => "City",
                'action' => "delete-city",
                "description" => "Delete city entity."
            ],
            [
                'topic' => "City",
                'action' => "display-create-city-form",
                "description" => "Display city create form."
            ],
            [
                'topic' => "City",
                'action' => "update-city",
                "description" => "Update city entity."
            ],
            [
                'topic' => "City",
                'action' => "create-city",
                "description" => "Create city entity."
            ],
            [
                'topic' => "City",
                'action' => "list-city",
                "description" => "List all cities."
            ],
            [
                'topic' => "Activity",
                'action' => "display-edit-activity-form",
                "description" => "Display activity edit form."
            ],
            [
                'topic' => "Activity",
                'action' => "delete-activity",
                "description" => "Delete activity entity."
            ],
            [
                'topic' => "Activity",
                'action' => "display-create-activity-form",
                "description" => "Display activity create form."
            ],
            [
                'topic' => "Activity",
                'action' => "update-activity",
                "description" => "Update activity entity."
            ],
            [
                'topic' => "Activity",
                'action' => "create-activity",
                "description" => "Create activity entity."
            ],
            [
                'topic' => "Activity",
                'action' => "list-activity",
                "description" => "List all activities."
            ],
            [
                'topic' => "Activity",
                'action' => "reorder-activity",
                "description" => "Reorder activities in activity listing."
            ],
            [
                'topic' => "Tour Guide Language",
                'action' => "display-edit-tour-guide-language-form",
                "description" => "Display tour guide language edit form."
            ],
            [
                'topic' => "Tour Guide Language",
                'action' => "delete-tour-guide-language",
                "description" => "Delete tour guide language entity."
            ],
            [
                'topic' => "Tour Guide Language",
                'action' => "display-create-tour-guide-language-form",
                "description" => "Display tour guide language create form."
            ],
            [
                'topic' => "Tour Guide Language",
                'action' => "update-tour-guide-language",
                "description" => "Update tour guide language entity."
            ],
            [
                'topic' => "Tour Guide Language",
                'action' => "create-tour-guide-language",
                "description" => "Create tour guide language entity."
            ],
            [
                'topic' => "Tour Guide Language",
                'action' => "list-tour-guide-language",
                "description" => "List all tour guide languages."
            ],
            [
                'topic' => "Currency",
                'action' => "display-edit-currency-form",
                "description" => "Display edit currency form."
            ],
            [
                'topic' => "Currency",
                'action' => "delete-currency",
                "description" => "Delete currency entity."
            ],
            [
                'topic' => "Currency",
                'action' => "display-create-currency-form",
                "description" => "Display currency create form."
            ],
            [
                'topic' => "Currency",
                'action' => "update-currency",
                "description" => "Update currency entity."
            ],
            [
                'topic' => "Currency",
                'action' => "create-currency",
                "description" => "Create currency entity."
            ],
            [
                'topic' => "Currency",
                'action' => "list-currency",
                "description" => "List all currencies."
            ],
            [
                'topic' => "Tag",
                'action' => "display-edit-tag-form",
                "description" => "Display edit tag form."
            ],
            [
                'topic' => "Tag",
                'action' => "delete-tag",
                "description" => "Delete tag entity."
            ],
            [
                'topic' => "Tag",
                'action' => "display-create-tag-form",
                "description" => "Display tag create form."
            ],
            [
                'topic' => "Tag",
                'action' => "update-tag",
                "description" => "Update tag entity."
            ],
            [
                'topic' => "Tag",
                'action' => "create-tag",
                "description" => "Create tag entity."
            ],
            [
                'topic' => "Tag",
                'action' => "list-tag",
                "description" => "List all tags."
            ],
            [
                'topic' => "Trip",
                'action' => "display-trip-creation-form",
                "description" => "Display trip creation form."
            ],
            [
                'topic' => "Trip",
                'action' => "create-new-trip",
                "description" => "Create new trip action."
            ],
            [
                'topic' => "Trip",
                'action' => "list-all-trips",
                "description" => "List all trips."
            ],
            [
                'topic' => "Trip",
                'action' => "list-own-trips",
                "description" => "List trips only created by user."
            ],
            [
                'topic' => "Trip",
                'action' => "view-all-trips",
                "description" => "View trip created by any user."
            ],
            [
                'topic' => "Trip",
                'action' => "view-own-trip",
                "description" => "View trip only created by user."
            ],
            [
                'topic' => "Trip",
                'action' => "delete-trip",
                "description" => "Delete trip action."
            ],
            [
                'topic' => "Trip",
                'action' => "display-trip-modification-form",
                "description" => "Display trip modification form."
            ],
            [
                'topic' => "Trip",
                'action' => "update-trip",
                "description" => "Update trip action."
            ],
            [
                "topic" => "Admin Panel",
                "action" => "admin-panel-access",
                "description" => "Allow user to access web admin panel"
            ],
            [
                "topic" => "User Invitation",
                "action" => "display-edit-user-invitation-form",
                "description" => "Display user invitation edit form"
            ],
            [
                "topic" => "User Invitation",
                "action" => "delete-user-invitation",
                "description" => "Delete user invitation action"
            ],
            [
                "topic" => "User Invitation",
                "action" => "display-create-user-invitation-form",
                "description" => "Display user invitation create form"
            ],
            [
                "topic" => "User Invitation",
                "action" => "update-user-invitation",
                "description" => "Update user invitation action"
            ],
            [
                "topic" => "User Invitation",
                "action" => "create-user-invitation",
                "description" => "Create user invitation action"
            ],
            [
                "topic" => "User Invitation",
                "action" => "list-user-invitation",
                "description" => "List all user invitations"
            ]
        ]);
    }
}