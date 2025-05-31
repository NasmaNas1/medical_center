<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatientsRelationManager extends RelationManager
{
    protected static string $relationship = 'patients';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('patient_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('patient_id')
            ->columns([

              Tables\Columns\TextColumn::make('uuid')
                ->label('المعرف الفريد')
                ->searchable()
                ->sortable(),

              Tables\Columns\TextColumn::make('name')
                ->label('الاسم')
                ->searchable()
                ->sortable(),

              Tables\Columns\TextColumn::make('email')
                ->label('البريد الإلكتروني')
                ->searchable()
                ->sortable(),

              Tables\Columns\TextColumn::make('phone')
                ->label('الهاتف')
                ->searchable()
                ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('بحث بالاسم'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['name'],
                                fn ($query) => $query->where('name', 'like', '%'.$data['name'].'%')
                            );
                    })
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);

        }
    }

