<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageProjectsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function guests_cannot_create_projects()
    {
        $attributes = Project::factory()->raw();
        $this->post("/projects", $attributes)->assertRedirect('login');
    }

    /**
     * @test
     */
    public function guests_cannot_view_projects()
    {
        $this->get("/projects")->assertRedirect('login');
    }

    /**
     * @test
     */
    public function guests_cannot_view_a_single_projects()
    {
        $project = Project::factory()->create();
        $this->get($project->path())->assertRedirect('login');
    }

    /**
     * @test
     */
    public function a_user_can_create_a_project()
    {
        $this->actingAs(User::factory()->create());
        $this->get("/projects/create")->assertStatus(200);

        $attributes = Project::factory()->raw();
        $this->post("/projects", $attributes)->assertRedirect("/projects");
        $attributes['owner_id'] = auth()->id();

        $this->assertDatabaseHas("projects", $attributes);
        $this->get("/projects")->assertSee($attributes["title"]);
    }

    /**
     * @test
     */
    public function a_user_can_view_their_project()
    {
        $this->be(User::factory()->create());
        $project = Project::factory()->create(['owner_id' => auth()->id()]);

        $this->get($project->path())
            ->assertSee($project->title)
            ->assertSee($project->description);
    }

    /**
     * @test
     */
    public function a_authenticated_user_cannot_view_others_project()
    {
        $this->be(User::factory()->create());
        $project = Project::factory()->create();

        $this->get($project->path())->assertStatus(403);
    }

    /**
     * @test
     */
    public function a_project_requires_a_title()
    {
        $this->actingAs(User::factory()->create());
        $attributes = Project::factory()->raw(['title' => '']);
        $this->post("/projects", $attributes)->assertSessionHasErrors("title");
    }

    /**
     * @test
     */
    public function a_project_requires_a_description()
    {
        $this->actingAs(User::factory()->create());
        $attributes = Project::factory()->raw(['description' => '']);
        $this->post("/projects", $attributes)->assertSessionHasErrors("description");
    }

}
