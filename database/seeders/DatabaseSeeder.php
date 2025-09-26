<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\PeerReview;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Task;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Departments
        $departments = Department::factory()->count(5)->create();

        // Users (50) assigned randomly to departments
        $users = User::factory()->count(50)->create([
            // department_id will be overridden per user below
        ])->each(function (User $user) use ($departments) {
            $user->department_id = $departments->random()->id;
            $user->save();
        });

        // Trainings for current year
        $trainings = Training::factory()->count(5)->create([
            'required' => true,
            'year' => now()->year,
        ]);

        // Projects with milestones
        $projects = Project::factory()->count(10)->create([
            // department_id will be auto via factory
        ]);
        $projects->each(function (Project $project) {
            ProjectMilestone::factory()->count(random_int(3, 6))->create([
                'project_id' => $project->id,
            ]);
        });

        // Tasks across projects assigned to random users
        foreach ($projects as $project) {
            Task::factory()->count(random_int(10, 25))->create([
                'project_id' => $project->id,
                'assignee_id' => $users->random()->id,
            ]);
        }

        // Attach trainings to users with pivot dates
        foreach ($users as $user) {
            foreach ($trainings as $training) {
                $assigned = now()->subMonths(random_int(0, 8))->toDateString();
                $completed = random_int(0, 1) ? now()->subWeeks(random_int(0, 8))->toDateString() : null;
                $user->trainings()->syncWithoutDetaching([
                    $training->id => [
                        'assigned_date' => $assigned,
                        'completed_date' => $completed,
                    ],
                ]);
            }
        }

        // Peer reviews: ~5 reviewers per user
        foreach ($users as $reviewee) {
            $reviewers = $users->where('id', '!=', $reviewee->id)->shuffle()->take(5);
            foreach ($reviewers as $reviewer) {
                PeerReview::factory()->create([
                    'reviewee_id' => $reviewee->id,
                    'reviewer_id' => $reviewer->id,
                ]);
            }
        }
    }
}
