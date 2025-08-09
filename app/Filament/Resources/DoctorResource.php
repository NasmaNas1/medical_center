<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorResource\Pages;
use App\Filament\Resources\DoctorResource\RelationManagers;
use App\Models\Doctor;
use App\Models\Specialization;
use Illuminate\Support\Facades\Hash;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;
    protected static ?string $navigationGroup = 'Doctors';
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $modelLabel = 'Doctor';
    protected static ?string $pluralModelLabel = 'Doctors';
    protected static ?string $navigationLabel = 'Doctors';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('إضافة طبيب جديد')
                    ->icon('heroicon-s-user-plus')
                    ->collapsible()
                    ->description('املأ جميع الحقول المطلوبة')
                    ->schema([
                        TextInput::make('name')
                            ->label('الاسم الكامل')
                            ->required()
                            ->columnSpan(1),
                            
                        Select::make('specialization_id')
                            ->label('الاختصاصات')
                            ->relationship('Specialization','type')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),
                            
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),
                            
                        FileUpload::make('image')
                            ->label('صورة الطبيب')
                            ->disk('public')
                            ->directory('doctors')
                            ->image()
                            ->maxSize(5000)
                            ->columnSpan(1),
                            
                        TextInput::make('practice')
                            ->label('سنوات الخبرة')
                            ->numeric()
                            ->columnSpan(1),
                            
                        MarkdownEditor::make('about_doctor')
                            ->label('السيرة الذاتية')
                            ->required()
                            ->columnSpanFull(),

                        // حقل كلمة السر للإنشاء
                        TextInput::make('password')
                            ->label('كلمة السر')
                            ->password()
                            ->revealable()
                            ->required()
                            ->visibleOn('create')
                            ->columnSpan(1)
                            ->helperText('أدخل كلمة السر المطلوبة'),
                            
                        // حقل كلمة السر للتعديل
                        TextInput::make('password')
                            ->label('كلمة السر الجديدة')
                            ->password()
                            ->revealable()
                            ->visibleOn('edit')
                            ->columnSpan(1)
                            ->helperText('أدخل كلمة السر الجديدة')
                    ])
                    ->columns(2)
            ]);
    }

    public static function afterSave($record, $data): void
    {
        // لا حاجة لتشفير خاص - ستكون كلمة السر كما أدخلتها
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->disk('public')
                    ->width(80)
                    ->height(90)
                    ->extraImgAttributes([
                        'class' => 'rounded-full object-cover',
                    ]),
                    
                TextColumn::make('name'),
                TextColumn::make('specialization.type')->label('الاختصاص'),
                TextColumn::make('email'),
                TextColumn::make('practice')->label('سنوات الخبرة'),
                
                TextColumn::make('password')
    ->label('كلمة السر')
    ->state(function ($record) {
        return $record->password ? '*****' : 'غير معين';
    })
    ->tooltip('انقر لإظهار كلمة السر')
    ->extraAttributes(['class' => 'cursor-pointer'])
    ->action( // استخدم action() بدلاً من actions()
        Tables\Actions\Action::make('showPassword')
            ->label('إظهار')
            ->action(function ($record) {
                Notification::make()
                    ->title('كلمة السر')
                    ->body("كلمة السر: {$record->password}")
                    ->success()
                    ->persistent()
                    ->send();
            })
    ),
                    
                TextColumn::make('about_doctor')->label('السيرة الذاتية'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('specialization_id')
                    ->label('الاختصاص')
                    ->relationship('specialization', 'type')
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                
                // إزالة زر إعادة التعيين العشوائي
                
                Tables\Actions\Action::make('patients')
                    ->label('المرضى')
                    ->icon('heroicon-o-user-group')
                    ->color('gray')
                    ->button()
                    ->url(fn ($record) => DoctorResource::getUrl('view', [
                        'record' => $record->id,
                        'activeRelationManager' => 'patients'
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PatientsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
            'view' => Pages\ViewDoctor::route('/{record}'),
        ];
    }
}