<?php

namespace App\Filament\Student\Resources;

use App\Filament\Student\Resources\CourseResource\Pages;
use App\Filament\Student\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use App\Models\StudentCourse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseResource extends Resource
{
    protected static ?string $model = StudentCourse::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'My Courses';

    protected static ?int $navigationSort = 10;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('student', function (Builder $query) {
                $query->where('user_id', auth()->id());
            });
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()
            ->where('status', '!=', 'done')
            ->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('course.session')
                    ->label('Total Session')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'waiting_schedule' => 'warning',
                        'schedule_not_set' => 'danger',
                        'done' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('upcoming_schedule')
                    ->label('Next Schedule')
                    ->state(function (StudentCourse $record) {
                        $upcomingSchedule = \App\Models\Schedule::where('student_course_id', $record->id)
                            ->where('start_date', '>', now())
                            ->orderBy('start_date', 'asc')
                            ->first();

                        return $upcomingSchedule ? $upcomingSchedule->start_date : null;
                    })
                    ->date('M d, Y - H:i')
                    ->badge()
                    ->color('success')
                    ->placeholder('Not scheduled yet'),
                Tables\Columns\TextColumn::make('instructor.name')
                    ->label('Instructor')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view_schedule')
                    ->label('Schedule Details')
                    ->icon('heroicon-o-calendar')
                    ->color('info')
                    ->modalHeading(fn(StudentCourse $record) => "Schedule for {$record->course->name}")
                    ->modalContent(function (StudentCourse $record) {
                        $schedules = \App\Models\Schedule::where('student_course_id', $record->id)->get();
                        if ($schedules->isEmpty()) {
                            return 'No schedules found for this course.';
                        }
                        return view('filament.student.schedules-list', [
                            'schedules' => $schedules,
                        ]);
                    })
                    ->modalSubmitActionLabel('Open Schedule Page')
                    ->modalCancelAction(false)
                    ->action(function (StudentCourse $record) {
                        return redirect()->route('filament.student.resources.schedules.index');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCourses::route('/'),
        ];
    }
}
