<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Scopes\FilterScope;
use App\Models\Scopes\IncludeScope;
use App\Models\Scopes\SelectScope;
use App\Models\Scopes\SortScope;
//use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'activo',
        'type'    // "0"=>Mucama
                  // "1"=>Empleado
                  // "2"=>Administrador
                  // "3"=>Cliente
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted(): void
    {
        static::addGlobalScopes([
            //FilterScope::class,
            //SelectScope::class,
            //SortScope::class,
            //IncludeScope::class
        ]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }


    public function scopeGetOrPaginate($query)
    {
        
        if(request('select')){
             $select = request('select');
             $selectArray = explode(',', $select);
             $query->select($selectArray);
        }
    
    
        if (request('include')) {
            $include = explode(',', request('include'));
            $query->with($include);
        }

        
        

        if(request('filters')){
            $filters = request('filters');
            foreach ($filters as $field => $conditions) {
                foreach ($conditions as $operator => $value) {
                    if (in_array($operator, ['=', '>', '<', '>=', '<=', '!='])) {
                        $query->where($field, $operator, $value);
                    } 

                    if ($operator == 'like') {
                        $query->where($field, 'like', "%$value%");
                    }
                }
            }
        }


        if(request('sort')){
            $sortFields = explode(',', request('sort'));

            foreach ($sortFields as $sortField) {
                
                $direction = 'asc';

                if (substr($sortField, 0, 1) == '-') {
                    $direction = 'desc';
                    $sortField = substr($sortField, 1);
                }

                $query->orderBy($sortField, $direction);
            }
        }

        // $sortFields = explode(',', request('sort'));

        // foreach ($sortFields as $sortField) {
        //     
        //     $direction = 'asc';

        //     if (substr($sortField, 0, 1) == '-') {
        //         $direction = 'desc';
        //         $sortField = substr($sortField, 1);
        //     }

        //     $query->orderBy($sortField, $direction);
        // }

        /* if(request('flags')){
            $flags = request('flags');
            if(isset($flags['id_in_servs'])){
                $userId = $flags['id_in_servs'];
                $query->whereJsonContains('users->' . $userId, [
                    'id' => (int)$userId,
                ]);
            }
        } */

        /* if(request('inJSON')){
            $filters = request('inJSON');
            
            foreach ($filters as $field => $value) {
                if ($this->relationLoaded($field) || method_exists($this, $field)) {
                    $query->whereJsonContains($field . '->' . $value, ['id' => $value]);
                }
            }
        } */

        //$query = Service::whereJsonContains('users->' . $this->id, ['id' => $this->id]);

        
        //dd($query->toSql(), $query->getBindings());

        if (request()->has('perPage')) {

            return $query->paginate(request()->query('perPage'));
        }


        

        return $query->get();
        
    }


    // Relación uno a muchos con UserData
    public function userdata()
    {
        return $this->hasOne(UserData::class);  
    }

    public function permissions()
    {
        // Devuelve todos los permisos asignados directamente al modelo User
        // Compatible con la estructura de tablas de Spatie (model_has_permissions)
        return $this->belongsToMany(
            Permission::class,
            'model_has_permissions',
            'model_id',
            'permission_id'
        )->wherePivot('model_type', static::class);
    }

    /**
     * Devuelve todos los permisos asignados al usuario a través de sus roles.
     * Retorna una colección de Permission única.
     */
    public function permissionsViaRoles()
    {
        return Permission::query()
            ->select('permissions.*')
            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->join('model_has_roles', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', static::class)
            ->where('model_has_roles.model_id', $this->getKey())
            ->distinct();
    }

    public function unities()
    {
        return $this->belongsToMany(Unity::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function books()
    {
        return $this->morphMany(Book::class, 'bookable');
    }

    public function jornadas()
    {
        return $this->hasMany(Jornada::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function servsuser($fecha_inicio = null, $fecha_fin = null)
    {
        $query = Service::whereJsonContains('users->' . $this->id, ['id' => $this->id]);
        if ($fecha_inicio && $fecha_fin) {
            $query->whereBetween('fecha_inicio', [$fecha_inicio, $fecha_fin]);
        }

        return $query;
    }
}
