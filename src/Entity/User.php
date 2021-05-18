<?php

namespace App\Entity;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\Auth\Register;
use App\Controller\Auth\Login;
use App\Controller\Auth\ResetAction;
use App\Controller\Auth\ResetRequest;
use App\Controller\Auth\ChangePassword;
use App\Controller\Users\AddUser;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\Auth\EditUser;
use App\Controller\Users\GetUserInfo;
use App\Controller\Users\UnscheduledEmployee;
use App\Controller\Users\EligibleOpenAndReplace;
use App\Controller\Users\GetShiftCount;
use App\Controller\Auth\SetPassword;
use App\Controller\Users\ScheduledEmployee;
use App\Controller\Auth\ConfirmMobile;
use App\Controller\Auth\ResendVerification;
use App\Controller\Auth\ConfirmEmailByToken;
use App\Controller\Auth\GetAvailableMobileCode;

//@todo:you should add custom query extension like for getting user objects https://api-platform.com/docs/core/extensions/
/**
 * @ApiResource(
 *     normalizationContext={"groups"={"userread","register","find_result"}},
 *     denormalizationContext={"groups"={"userwrite","register","login","reset_request","find"}},
 *
 *     itemOperations={
 *     "delete",
 *     "put"={"controller"=EditUser::class,"denormalization_context"={"groups"={"userwrite"} } ,"deserialize"=false ,"write"=false ,"attributes"={"_api_receive":false}},
 *     "get"
 *    },
 *
 *     collectionOperations={
 *          "refresh-token"={
 *          "method"="Post",
 *          "route_name"="gesdinet_jwt_refresh_token",
 *          "swagger_context"={
 *          "parameters"={{
 *                       "name"="payload",
 *                       "type"="object",
 *                       "in"="body",
 *                       "properties"={"refresh_token"={"type"="string"} }
 *          }}
 *      }
 *     },
 *     "get"={"normalization_context"={"groups"={"userread"}} },
 *     "get_system_roles"={
 *          "method"="GET",
 *          "route_name"="get_available_roles",
 *          "swagger_context"={
 *              "summary":"get available role of sysytems"}
 *     },

 *     "get_timezone"={
 *          "method"="GET",
 *          "route_name"="get_timezone",
 *          "swagger_context"={

 *              "summary":"get available timezone of sysytems",
 *              "responses":{
 *                  "200":{
 *                      "description":"get available timezones"

 *                  }

 *              }
 *          }
 *     },
 *     "user_login" :{
 *          "method"="POST",
 *          "path"="/login",
 *          "controller"=Login::class,
 *          "denormalization_context"={"groups"={"login"}},
 *          "swagger_context"={

 *              "summary":"login user",
 *              "responses"={
 *                   200={
 *                      "description":"token send successfully"
 *                  },
 *                  401={
 *                      "description":"bad credential"
 *                  }
 *
 *              }
 *          }
 *
 *     },
 *     "reset_request":{

 *          "method"="POST",
 *          "path"="/reset_request",
 *          "controller"=ResetRequest::class,
 *          "denormalization_context"={"groups"={"reset_request"}},
 *          "swagger_context"={
 *              "summary"="reset request forgot password email"

 *          }
 *
 *     },
 *     "reset_password"={

 *          "method"="POST",
 *          "path"="/reset_action",
 *          "controller"=ResetAction::class,
 *          "defaults"={"_api_receive"=false},
 *          "swagger_context"={
 *              "summary"="request with username for forgotpassword email",
 *              "parameters"={{
 *                  "name"="payload",
 *                  "type"="object",
 *                  "in"="body",
 *                  "properties"={
 *                      "token"={
 *                          "type"="string"

 *                      },
 *                      "plain_password"={
 *                          "type"="object",
 *                          "example"={
 *                              "first"="string",
 *                              "second"="string"

 *                          }

 *                      }

 *                  }

 *              }},
 *              "responses"={
 *                   201={
 *                      "description":"email send successfully"
 *                  },
 *                  404={
 *                      "description":"token expired or corrupt"

 *                  },
 *                 400={
 *                      "description":"bad params"

 *                 }
 *              }
 *          }
 *     },
 *     "change_password":{
 *          "method"="POST",
 *          "path"="/change_password",
 *          "controller"=ChangePassword::class,
 *          "defaults"={"_api_receive"=false},
 *          "swagger_context"={
 *              "summary"="users change their password",
 *              "parameters"={{
 *                  "name"="payload",
 *                  "type"="object",
 *                  "in"="body",
 *                  "properties"={
 *                      "last_password"={
 *                          "type"="string"

 *                      },
 *                      "new_password"={
 *                          "type"="object",
 *                          "example"={
 *                              "first"="string",
 *                              "second"="string"

 *                          }

 *                      }

 *                  }

 *              }}

 *          }

 *     },

 *    "user_register":{
 *          "method"="POST",
 *          "path"="/register",
 *          "controller"=Register::class,
 *          "swagger_context"={"summary":"register a user"},
 *          "denormalization_context"={"groups"={"register"} },
 *          "normalization_context"={"groups"={"register_read"} }
 *
 *     },
 *
 *     "user_set_password"={
 *          "method"="POST",
 *          "path"="/set-password",
 *           "shortName"="setPassword",
 *          "controller"=SetPassword::class,
 *          "swagger_context"={
 *              "summary":"set password for first time",
 *              "parameters"={{ "name"="payload","in"="body","properties"={"password":{"type":"string"},"token":{"type":"string"} } }}
 *          },
 *
 *     },

 *     "add_user"={
 *          "method"="Post",
 *          "path"="/users",
 *          "validate"=false,
 *          "write"=false,
 *          "denormalization_context"={"groups"={"userwrite"}},
 *          "controller"=AddUser::class,
 *          "access_control"="is_granted('BUSINESS_SUPERVISOR')"
 *
 *     },
 *     "get_user_info"={
 *          "method"="get",
 *          "path"="/users/info",
 *          "controller"=GetUserInfo::class
 *     },
 *      "get_eligible"={
 *          "method"="Post",
 *          "controller"=EligibleOpenAndReplace::class,
 *          "path"="/users/get_eligible",
 *          "swagger_context"={
 *              "summary"="find eligible employee for shifts",
 *              "parameters"={{
 *                  "name"="payload",
 *                  "in"="body",
 *                  "type"="object",
 *                  "properties"={
 *                      "positionId"={"type"="string"},
 *                      "scheduleId"={"type"="string"},
 *                      "startTime"={"type"="string"},
 *                      "endTime"={"type"="string"},

 *                  }

 *              }}

 *          }
 *     },
 *     "get_unscheduled_user"={
 *          "method"="Post",
 *          "path"="/users/unscheduled",
 *          "controller"=UnscheduledEmployee::class,
 *          "swagger_context"={
 *              "summary"="find unscheduled employee in a date range",
 *              "parameters"={
 *                  {
 *                      "name"="payload",
 *                      "in"="body",
 *                      "type"="object",
 *                      "properties"={"start_date":{"type"="string"},"end_date"={"type":"string"},"schedule"={"type":"string"}}

 *                  }} }  },
 *      "get_scheduled_user"={
 *          "method"="Post",
 *          "path"="/users/scheduled",
 *          "controller"=ScheduledEmployee::class,
 *          "swagger_context"={
 *              "summary"="find scheduled employee in a date range",
 *              "parameters"={
 *                  {
 *                      "name"="payload",
 *                      "in"="body",
 *                      "type"="object",
 *                      "properties"={"start_date":{"type"="string"},"end_date"={"type":"string"}}

 *                  }} }  },
 *      "get_user_shift_count"={
 *          "method"="post",
 *          "path"="/users/shift_count",
 *          "controller"=GetShiftCount::class,
 *          "swagger_context"={
 *              "parameters"={{
 *                  "name"="payload",
 *                  "type"="object",
 *                  "in"="body",
 *                  "properties"={"user"={"type"="string"}}
 *              }}

 *          }

 *     },
 *     "verification_code_confirm"={
 *          "method"="post",
 *          "controller"=ConfirmMobile::class,
 *          "path"="/users/verification_confirm",
 *          "swagger_context"={
 *              "parameters"={{
 *                  "name"="payload",
 *                  "type"="object",
 *                  "in"="body",
 *                  "properties"={"code"={"type"="string"}, "user"={"type"="string" } }

 *              }}

 *          }

 *     },
 *     "resendVerification"={
 *          "method"="post",
 *          "controller"=ResendVerification::class,
 *          "path"="/users/resend_code",
 *          "swagger_context"={
 *              "parameters"={{
 *                  "name"="payload",
 *                  "type"="object",
 *                  "in"="body",
 *                  "properties"={"phone"={"type"="string"} }
 *              }}

 *          }

 *     },
 *     "get_mobile_code"={
 *          "method"="get",
 *          "path"="/users/get_mobile_code",
 *          "controller"=GetAvailableMobileCode::class

 *     },
 *      "confirmEmail"={
 *          "route_name"="confirm_email"
 *      }
 *     },
 *
 *
 *
 *     )
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="It looks like your already have an account with this email!",groups={"register","userwrite"})
 * @UniqueEntity(fields={"mobile"}, message="It looks like your already have an account withi this phone!",groups={"register","userwrite"})
 * @ApiFilter(SearchFilter::class,properties={"userHasSchedule":"exact","positions":"exact"})
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups({"userread","find_result","readtimeoffreq","shiftread","readbusinessreq","register_read"})
     */
    private $id;
    //@todo :add dynamic serialization https://api-platform.com/docs/core/serialization/#changing-the-serialization-context-dynamically
    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotNull
     * @Assert\Email(groups={"reset_request","register","add"})
     * @Groups({"register","register_read","login","reset_request","userwrite","userread","find","shiftread","read_request","readtimeoffreq"})
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     *  @Groups({"userwrite","userread","find_result","readtimeoffreq","readbusinessreq"})
     */
    private $roles=['ROLE_USER'] ;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string",nullable=true)
     * @Assert\NotBlank(groups={"login"})
     * @Groups({"login","register"})
     */
    private $password;

    /**
     * @var PasswordType $plainPassword
     * @Groups({"reset_action"})
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"add","register"})
     * @Groups({"register","register_read","userwrite","userread","find_result","readtimeoffreq","readbusinessreq","shiftread","annotation_read","read_request","read_results","read_attendance_times"})
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"add","register"})
     * @Groups({"register","register_read","userwrite","userread","find_result","readtimeoffreq","readbusinessreq","shiftread","annotation_read","read_request","read_results","read_attendance_times"})
     *
     */
    private $lastName;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"register","register_read","userwrite","userread"})
     * @Assert\Regex(pattern="/^(\(?\+?[0-9]*\)?)?[0-9_\- \(\)]*$/",message="the phone number is not valid",groups={"add","register"})
     */
    private $mobile;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"userwrite","userread"})
     */
    private $preferredHoursWeekly=0;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"userwrite","userread"})
     */
    private $privacy=false;



    /**
     *  @ORM\Column(type="boolean",nullable=false)
     *  @Groups({"userwrite","register","register_read"})
     */
    private $useCustomTimezone=true;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"userwrite","userread"})
     */
    private $note;



    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Position", mappedBy="employee_has_position",  cascade="persist")
     * @Groups({"userread","userwrite","read_attendance_times"})
     */
    private $positions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Availability", mappedBy="user", orphanRemoval=true)
     *
     */
    private $availabilities;



    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Schedule", inversedBy="users")
     * @Groups({"userread","userwrite","userread","read_attendance_times"})
     */
    private $userHasSchedule;


    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Industry", inversedBy="users")
     * @Groups({"userread","userwrite","userread","read_attendance_times"})
     */
    private $userHasIndustries;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Shift", mappedBy="ownerId")
     */
    private $shifts;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TimeOffRequest", mappedBy="userID", orphanRemoval=true)
     */
    private $timeOffRequests;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TimeOffRequest", mappedBy="userCreatorId", orphanRemoval=true)
     */
    private $requested_timeoff;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_reset_request;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BusinessRequest", mappedBy="userId", orphanRemoval=true)
     */
    private $businessRequests;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserBusinessRole", mappedBy="user", orphanRemoval=true,cascade={"persist"})
     * @Groups({"userwrite","userread","shiftread"})
     * @Assert\NotNull(groups={"add","user_business_read"})
     */
    private $userBusinessRoles;



    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ShiftRequest", mappedBy="requesterId", orphanRemoval=true)
     */
    private $shiftRequest;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"userwrite","userread","register","register_read"})
     */
    private $timezone="Europe/London";

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EmployeeAlert", mappedBy="userId", orphanRemoval=true)
     * @Groups({"userread"})
     */
    private $employeeAlerts;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Shift", mappedBy="eligibleOpenShiftUser")
     */
    private $eligibleForOpenShift;



    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SwapUserShiftAccept", mappedBy="user", orphanRemoval=true)
     */
    private $swapUserShiftAccepts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AttendanceTimes", mappedBy="user", orphanRemoval=true)
     */
    private $attendanceTimes;

    /**
     * @Groups({"read_attendance_times","userread"})
     */
    private $lastAttendanceTime;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ImeiUser", mappedBy="user", orphanRemoval=true)
     */
    private $imeiUsers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PeriodStaffResult", mappedBy="user", orphanRemoval=true)
     */
    private $periodStaffResults;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TimeOffTotal", mappedBy="user", orphanRemoval=true,cascade={"persist"})
     */
    private $timeOffTotals;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AttendanceTimesLog", mappedBy="user", orphanRemoval=true)
     */
    private $attendanceTimesLogs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Notification", mappedBy="user", orphanRemoval=true)
     */
    private $notifications;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentHistory", mappedBy="user")
     */
    private $paymentHistories;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\NotificationHistory", mappedBy="user", orphanRemoval=true)
     */
    private $notificationHistories;

    /**
     * @ORM\ManyToOne(targetEntity=Media::class)
     * @Groups({"userwrite","userread"})
     */
    private $image;

    /**
     * @ORM\Column(type="boolean")
     */
    private $mobileIsConfirmed=true;

    /**
     * @var boolean $confirmWithSms
     *  @Groups({"register"})
     */
    private $confirmWithSms=false;


    public function __construct()
    {
        $this->positions = new ArrayCollection();
        $this->availabilities = new ArrayCollection();
        $this->userHasSchedule = new ArrayCollection();
        $this->userHasIndustries = new ArrayCollection();
        $this->shifts = new ArrayCollection();
        $this->timeOffRequests = new ArrayCollection();
        $this->businessRequests = new ArrayCollection();
        $this->userBusinessRoles = new ArrayCollection();
        $this->shiftRequest = new ArrayCollection();
        $this->employeeAlerts = new ArrayCollection();
        $this->eligibleForOpenShift = new ArrayCollection();
        $this->swapUserShiftAccepts = new ArrayCollection();
        $this->attendanceTimes = new ArrayCollection();
        $this->imeiUsers = new ArrayCollection();
        $this->periodStaffResults = new ArrayCollection();
        $this->timeOffTotals = new ArrayCollection();
        $this->attendanceTimesLogs = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->paymentHistories = new ArrayCollection();
        $this->notificationHistories = new ArrayCollection();

    }

    /**
     * @return string
     */
    public function __toString(){

        return $this->email;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword( $plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }



    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }


    /**
     * @return Collection|Industry[]
     */
    public function getUserHasIndustries(): Collection
    {
        return $this->userHasIndustries;
    }

    public function addUserHasIndustries(Industry $industry): self
    {
        if (!$this->userHasIndustries->contains($industry)) {
            $this->userHasIndustries[] = $industry;
        }

        return $this;
    }

    public function removeUserHasIndustries(Industry $industry): self
    {
        if ($this->userHasIndustries->contains($industry)) {
            $this->userHasIndustries->removeElement($industry);
        }

        return $this;
    }


    public function getPreferredHoursWeekly(): ?int
    {
        return $this->preferredHoursWeekly;
    }

    public function setPreferredHoursWeekly(?int $preferredHoursWeekly): self
    {
        $this->preferredHoursWeekly = $preferredHoursWeekly;

        return $this;
    }

    public function getPrivacy(): ?bool
    {
        return $this->privacy;
    }

    public function setPrivacy(bool $privacy): self
    {
        $this->privacy = $privacy;

        return $this;
    }


    public function getUseCustomTimezone(): ?bool
    {
        return $this->useCustomTimezone;
    }

    public function setUseCustomTimezone(bool $useCustomTimezone): self
    {
        $this->useCustomTimezone = $useCustomTimezone;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }


    /**
     * @return Collection|Position[]
     */
    public function getPositions(): Collection
    {
        return $this->positions;
    }

    public function addPosition(Position $position): self
    {
        if (!$this->positions->contains($position)) {
            $this->positions[] = $position;
            $position->addEmployeeHasPosition($this);
        }

        return $this;
    }

    public function removePosition(Position $position): self
    {
        if ($this->positions->contains($position)) {
            $this->positions->removeElement($position);
            $position->removeEmployeeHasPosition($this);
        }

        return $this;
    }

    /**
     * @return Collection|Availability[]
     */
    public function getAvailabilities(): Collection
    {
        return $this->availabilities;
    }

    public function addAvailability(Availability $availability): self
    {
        if (!$this->availabilities->contains($availability)) {
            $this->availabilities[] = $availability;
            $availability->setUser($this);
        }

        return $this;
    }

    public function removeAvailability(Availability $availability): self
    {
        if ($this->availabilities->contains($availability)) {
            $this->availabilities->removeElement($availability);
            // set the owning side to null (unless already changed)
            if ($availability->getUser() === $this) {
                $availability->setUser(null);
            }
        }

        return $this;
    }



    /**
     * @return Collection|Schedule[]
     */
    public function getUserHasSchedule(): Collection
    {
        return $this->userHasSchedule;
    }

    public function addUserHasSchedule(Schedule $userHasSchedule): self
    {
        if (!$this->userHasSchedule->contains($userHasSchedule)) {
            $this->userHasSchedule[] = $userHasSchedule;
        }

        return $this;
    }

    public function removeUserHasSchedule(Schedule $userHasSchedule): self
    {
        if ($this->userHasSchedule->contains($userHasSchedule)) {
            $this->userHasSchedule->removeElement($userHasSchedule);
        }

        return $this;
    }


    /**
     * @return Collection|Shift[]
     */
    public function getShifts(): Collection
    {
        return $this->shifts;
    }

    public function addShift(Shift $shift): self
    {
        if (!$this->shifts->contains($shift)) {
            $this->shifts[] = $shift;
            $shift->setOwnerId($this);
        }

        return $this;
    }

    public function removeShift(Shift $shift): self
    {
        if ($this->shifts->contains($shift)) {
            $this->shifts->removeElement($shift);
            // set the owning side to null (unless already changed)
            if ($shift->getOwnerId() === $this) {
                $shift->setOwnerId(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection|TimeOffRequest[]
     */
    public function getTimeOffRequests(): Collection
    {
        return $this->timeOffRequests;
    }

    public function addTimeOffRequest(TimeOffRequest $timeOffRequest): self
    {
        if (!$this->timeOffRequests->contains($timeOffRequest)) {
            $this->timeOffRequests[] = $timeOffRequest;
            $timeOffRequest->setUserId($this);
        }

        return $this;
    }

    public function removeTimeOffRequest(TimeOffRequest $timeOffRequest): self
    {
        if ($this->timeOffRequests->contains($timeOffRequest)) {
            $this->timeOffRequests->removeElement($timeOffRequest);
            // set the owning side to null (unless already changed)
            if ($timeOffRequest->getUserId() === $this) {
                $timeOffRequest->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TimeOffRequest[]
     */
    public function getRequestedTimeoff(): Collection
    {
        return $this->requested_timeoff;
    }

    public function addRequestedTimeoff(TimeOffRequest $requestedTimeoff): self
    {
        if (!$this->requested_timeoff->contains($requestedTimeoff)) {
            $this->requested_timeoff[] = $requestedTimeoff;
            $requestedTimeoff->setUserCreatorId($this);
        }

        return $this;
    }

    public function removeRequestedTimeoff(TimeOffRequest $requestedTimeoff): self
    {
        if ($this->requested_timeoff->contains($requestedTimeoff)) {
            $this->requested_timeoff->removeElement($requestedTimeoff);
            // set the owning side to null (unless already changed)
            if ($requestedTimeoff->getUserCreatorId() === $this) {
                $requestedTimeoff->setUserCreatorId(null);
            }
        }

        return $this;
    }


    public function getLastResetRequest()
    {
        return $this->last_reset_request;
    }

    public function setLastResetRequest( $last_reset_request): self
    {
        $this->last_reset_request = $last_reset_request;

        return $this;
    }

    /**
     * @return Collection|BusinessRequest[]
     */
    public function getBusinessRequests(): Collection
    {
        return $this->businessRequests;
    }

    public function addBusinessRequest(BusinessRequest $businessRequest): self
    {
        if (!$this->businessRequests->contains($businessRequest)) {
            $this->businessRequests[] = $businessRequest;
            $businessRequest->setUserId($this);
        }

        return $this;
    }

    public function removeBusinessRequest(BusinessRequest $businessRequest): self
    {
        if ($this->businessRequests->contains($businessRequest)) {
            $this->businessRequests->removeElement($businessRequest);
            // set the owning side to null (unless already changed)
            if ($businessRequest->getUserId() === $this) {
                $businessRequest->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserBusinessRole[]
     */
    public function getUserBusinessRoles(): Collection
    {
        return $this->userBusinessRoles;
    }

    public function addUserBusinessRole(UserBusinessRole $userBusinessRole): self
    {
        if (!$this->userBusinessRoles->contains($userBusinessRole)) {
            $this->userBusinessRoles[] = $userBusinessRole;
            $userBusinessRole->setUser($this);
        }

        return $this;
    }

    public function removeUserBusinessRole(UserBusinessRole $userBusinessRole): self
    {
        if ($this->userBusinessRoles->contains($userBusinessRole)) {
            $this->userBusinessRoles->removeElement($userBusinessRole);
            // set the owning side to null (unless already changed)
            if ($userBusinessRole->getUser() === $this) {
                $userBusinessRole->setUser(null);
            }
        }

        return $this;
    }

    public function setUserBusinessRoles($userBusinessRole):self
    {
        $this->userBusinessRoles=$userBusinessRole;
        return $this;
    }



    public function getUserCacheKeyBusiness()
    {
       return 'userBusiness'.$this->getId();
    }

    /**
     * @return Collection|ShiftRequest[]
     */
    public function getShiftRequest(): Collection
    {
        return $this->shiftRequest;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * @return Collection|EmployeeAlert[]
     */
    public function getEmployeeAlerts(): Collection
    {
        return $this->employeeAlerts;
    }

    public function addEmployeeAlert(EmployeeAlert $employeeAlert): self
    {
        if (!$this->employeeAlerts->contains($employeeAlert)) {
            $this->employeeAlerts[] = $employeeAlert;
            $employeeAlert->setUserId($this);
        }

        return $this;
    }

    public function removeEmployeeAlert(EmployeeAlert $employeeAlert): self
    {
        if ($this->employeeAlerts->contains($employeeAlert)) {
            $this->employeeAlerts->removeElement($employeeAlert);
            // set the owning side to null (unless already changed)
            if ($employeeAlert->getUserId() === $this) {
                $employeeAlert->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Shift[]
     */
    public function getEligibleForOpenShift(): Collection
    {
        return $this->eligibleForOpenShift;
    }

    public function addEligibleForOpenShift(Shift $eligibleForOpenShift): self
    {
        if (!$this->eligibleForOpenShift->contains($eligibleForOpenShift)) {
            $this->eligibleForOpenShift[] = $eligibleForOpenShift;
            $eligibleForOpenShift->addEligibleOpenShiftUser($this);
        }

        return $this;
    }

    public function removeEligibleForOpenShift(Shift $eligibleForOpenShift): self
    {
        if ($this->eligibleForOpenShift->contains($eligibleForOpenShift)) {
            $this->eligibleForOpenShift->removeElement($eligibleForOpenShift);
            $eligibleForOpenShift->removeEligibleOpenShiftUser($this);
        }

        return $this;
    }


    /**
     * @return Collection|SwapUserShiftAccept[]
     */
    public function getSwapUserShiftAccepts(): Collection
    {
        return $this->swapUserShiftAccepts;
    }

    public function addSwapUserShiftAccept(SwapUserShiftAccept $swapUserShiftAccept): self
    {
        if (!$this->swapUserShiftAccepts->contains($swapUserShiftAccept)) {
            $this->swapUserShiftAccepts[] = $swapUserShiftAccept;
            $swapUserShiftAccept->setUser($this);
        }

        return $this;
    }

    public function removeSwapUserShiftAccept(SwapUserShiftAccept $swapUserShiftAccept): self
    {
        if ($this->swapUserShiftAccepts->contains($swapUserShiftAccept)) {
            $this->swapUserShiftAccepts->removeElement($swapUserShiftAccept);
            // set the owning side to null (unless already changed)
            if ($swapUserShiftAccept->getUser() === $this) {
                $swapUserShiftAccept->setUser(null);
            }
        }

        return $this;
    }



    /**
     * @return Collection|AttendanceTimes[]
     */
    public function getAttendanceTimes(): Collection
    {
        return $this->attendanceTimes;
    }

    public function addAttendanceTime(AttendanceTimes $attendanceTime): self
    {
        if (!$this->attendanceTimes->contains($attendanceTime)) {
            $this->attendanceTimes[] = $attendanceTime;
            $attendanceTime->setUser($this);
        }

        return $this;
    }

    public function removeAttendanceTime(AttendanceTimes $attendanceTime): self
    {
        if ($this->attendanceTimes->contains($attendanceTime)) {
            $this->attendanceTimes->removeElement($attendanceTime);
            // set the owning side to null (unless already changed)
            if ($attendanceTime->getUser() === $this) {
                $attendanceTime->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ImeiUser[]
     */
    public function getImeiUsers(): Collection
    {
        return $this->imeiUsers;
    }

    public function addImeiUser(ImeiUser $imeiUser): self
    {
        if (!$this->imeiUsers->contains($imeiUser)) {
            $this->imeiUsers[] = $imeiUser;
            $imeiUser->setUser($this);
        }

        return $this;
    }

    public function removeImeiUser(ImeiUser $imeiUser): self
    {
        if ($this->imeiUsers->contains($imeiUser)) {
            $this->imeiUsers->removeElement($imeiUser);
            // set the owning side to null (unless already changed)
            if ($imeiUser->getUser() === $this) {
                $imeiUser->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PeriodStaffResult[]
     */
    public function getPeriodStaffResults(): Collection
    {
        return $this->periodStaffResults;
    }

    public function addPeriodStaffResult(PeriodStaffResult $periodStaffResult): self
    {
        if (!$this->periodStaffResults->contains($periodStaffResult)) {
            $this->periodStaffResults[] = $periodStaffResult;
            $periodStaffResult->setUser($this);
        }

        return $this;
    }

    public function removePeriodStaffResult(PeriodStaffResult $periodStaffResult): self
    {
        if ($this->periodStaffResults->contains($periodStaffResult)) {
            $this->periodStaffResults->removeElement($periodStaffResult);
            // set the owning side to null (unless already changed)
            if ($periodStaffResult->getUser() === $this) {
                $periodStaffResult->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TimeOffTotal[]
     */
    public function getTimeOffTotals(): Collection
    {
        return $this->timeOffTotals;
    }

    public function addTimeOffTotal(TimeOffTotal $timeOffTotal): self
    {
        if (!$this->timeOffTotals->contains($timeOffTotal)) {
            $this->timeOffTotals[] = $timeOffTotal;
            $timeOffTotal->setUser($this);
        }

        return $this;
    }

    public function removeTimeOffTotal(TimeOffTotal $timeOffTotal): self
    {
        if ($this->timeOffTotals->contains($timeOffTotal)) {
            $this->timeOffTotals->removeElement($timeOffTotal);
            // set the owning side to null (unless already changed)
            if ($timeOffTotal->getUser() === $this) {
                $timeOffTotal->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AttendanceTimesLog[]
     */
    public function getAttendanceTimesLogs(): Collection
    {
        return $this->attendanceTimesLogs;
    }

    public function addAttendanceTimesLog(AttendanceTimesLog $attendanceTimesLog): self
    {
        if (!$this->attendanceTimesLogs->contains($attendanceTimesLog)) {
            $this->attendanceTimesLogs[] = $attendanceTimesLog;
            $attendanceTimesLog->setUser($this);
        }

        return $this;
    }

    public function removeAttendanceTimesLog(AttendanceTimesLog $attendanceTimesLog): self
    {
        if ($this->attendanceTimesLogs->contains($attendanceTimesLog)) {
            $this->attendanceTimesLogs->removeElement($attendanceTimesLog);
            // set the owning side to null (unless already changed)
            if ($attendanceTimesLog->getUser() === $this) {
                $attendanceTimesLog->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastAttendanceTime()
    {
        return $this->lastAttendanceTime;
    }

    /**
     * @param mixed $lastAttendanceTime
     */
    public function setLastAttendanceTime($lastAttendanceTime): void
    {
        $this->lastAttendanceTime = $lastAttendanceTime;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->contains($notification)) {
            $this->notifications->removeElement($notification);
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PaymentHistory[]
     */
    public function getPaymentHistories(): Collection
    {
        return $this->paymentHistories;
    }

    public function addPaymentHistory(PaymentHistory $paymentHistory): self
    {
        if (!$this->paymentHistories->contains($paymentHistory)) {
            $this->paymentHistories[] = $paymentHistory;
            $paymentHistory->setUser($this);
        }

        return $this;
    }

    public function removePaymentHistory(PaymentHistory $paymentHistory): self
    {
        if ($this->paymentHistories->contains($paymentHistory)) {
            $this->paymentHistories->removeElement($paymentHistory);
            // set the owning side to null (unless already changed)
            if ($paymentHistory->getUser() === $this) {
                $paymentHistory->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|NotificationHistory[]
     */
    public function getNotificationHistories(): Collection
    {
        return $this->notificationHistories;
    }

    public function addNotificationHistory(NotificationHistory $notificationHistory): self
    {
        if (!$this->notificationHistories->contains($notificationHistory)) {
            $this->notificationHistories[] = $notificationHistory;
            $notificationHistory->setUser($this);
        }

        return $this;
    }

    public function removeNotificationHistory(NotificationHistory $notificationHistory): self
    {
        if ($this->notificationHistories->contains($notificationHistory)) {
            $this->notificationHistories->removeElement($notificationHistory);
            // set the owning side to null (unless already changed)
            if ($notificationHistory->getUser() === $this) {
                $notificationHistory->setUser(null);
            }
        }

        return $this;
    }

    public function getImage(): ?Media
    {
        return $this->image;
    }

    public function setImage(?Media $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getMobileIsConfirmed(): ?bool
    {
        return $this->mobileIsConfirmed;
    }

    public function setMobileIsConfirmed(bool $mobileIsConfirmed): self
    {
        $this->mobileIsConfirmed = $mobileIsConfirmed;

        return $this;
    }

    public function getConfirmWithSms(): ?bool
    {
        return $this->confirmWithSms;
    }

    public function setConfirmWithSms(?bool $confirmWithSms): self
    {
        $this->confirmWithSms = $confirmWithSms;

        return $this;
    }





}

