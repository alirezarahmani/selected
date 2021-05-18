<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use App\Controller\Business\SetBankBusiness;
use App\Controller\Business\CompeleteBankAccount;
use App\Controller\Business\SetSuperAdminBankAccount;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Controller\Business\SetPayment;
use App\Controller\Business\SubmitPayment;
use App\Controller\BankRate\ExchangeBillingToCustomer;
use App\Controller\BankRate\ExchangeCostAddUser;
use App\Controller\Business\AdditionalUserPurchase;

/**
 * @ApiResource(collectionOperations={"post","get",
 *     "add_creditor"={
 *          "method":"post",
 *          "controller":SetSuperAdminBankAccount::class,
 *          "path":"/business_banks/add_creditor",
 *          "defaults"={"_api_receive"=false},
 *          "swagger_context"={
 *              "summary"="add creditor bank account for user  ****use vpn *****",
 *              "parameters"={{
 *                  "name"="payload",
 *                  "type"="object",
 *                  "in"="body",
 *                  "properties"={"name"={"type"="string","example":"selectedTime"} ,
 *                                "address"={"type"="string","example":"9 Acer Gardens"},
 *                                "city"={"type"="string","example":"Birmingham"},
 *                                "zipcode"={"type"="string","example":"B4 7NJ"},
 *                                "country"={"type"="string","example":"GB"},
 *                  }
 *              }},
 *              "responses"={
 *                   200={
 *                      "description":"return a url that user should be redirected to and a id that in success be check same with query params get"
 *                  },
 *                  401={
 *                      "description":"bad credential"
 *                  }
 *
 *              }
 *          }

 *     },
 *      "payment_page"={
 *          "method":"post",
 *          "controller":SetPayment::class,
 *          "path":"/business_banks/pay",
 *          "defaults"={"_api_receive"=false},
 *          "swagger_context"={
 *              "summary"="pay a billing by usaing pre set mandate and customer valid currency GBP,SEK,DKK,AUD,NZD,CAD,USD,EUR ****use vpn *****",
 *              "parameters"={{
 *                  "name"="payload",
 *                  "type"="object",
 *                  "in"="body",
 *                  "properties"={
 *                      "amount"={"type"="string","example":"10"},
 *                      "billing"={"type"="string","example":"/api/billings/1"},
 *                      "currency"={"type"="string","enum":{"GBP","SEK","DKK","AUD","NZD","CAD","USD","EUR"},"example":"GBP","description"="GBP,SEK,DKK,AUD,NZD,CAD,USD,EUR"}
 *                  }
 *              }},
 *              "responses"={
 *                   200={
 *                      "description":"return a url that user should be redirected to and a id that in success be check same with query params get"
 *                  },
 *                  401={
 *                      "description":"bad credential"
 *                  }
 *
 *              }
 *          }

 *     },
 *      "set_bank_detail"={
 *          "method"="get",
 *          "controller"=SetBankBusiness::class,
 *          "path"="/business_banks/set_bank",
 *          "defaults"={"_api_receive"=false},
 *          "swagger_context"={
 *              "summary"="get url to redirect to customers can create bank account  ****use vpn *****",
 *
 *              "responses"={
 *                   200={
 *                      "description":"return a url that user should be redirected to and a id that in success be check same with query params get"
 *                  },
 *                  401={
 *                      "description":"bad credential"
 *                  }
 *
 *              }
 *          }
 *      },
 *      "submit_payment"={
 *          "method"="POST",
 *          "controller"=SubmitPayment::class,
 *          "path"="/business_banks/submit_payment",
 *          "defaults"={"_api_receive"=false},
 *          "swagger_context"={
 *              "summary"=" ****read me ***** this api calls by gocardless webhooks and should not be used by you  ****read me *****",
 *
 *              "responses"={
 *                   200={
 *                      "description":"return a url that user should be redirected to and a id that in success be check same with query params get"
 *                  },
 *                  401={
 *                      "description":"bad credential"
 *                  }
 *
 *              }
 *          }
 *      },
 *     "complete_bank"={
 *          "method"="post",
 *          "controller"=CompeleteBankAccount::class,
 *          "path"="/business_banks/compelete",
 *          "defaults"={"_api_receive"=false},
 *          "swagger_context"={
 *              "summary"="compelete bank account  ****use vpn *****",
 *              "parameters"={{
 *                  "name"="payload",
 *                  "type"="object",
 *                  "in"="body",
 *                  "properties"={"redirect_flow_id"={"type"="string"}}
 *              }},
 *              "responses"={
 *                   200={
 *                      "description":"return a url that user should be redirected to and a id that in success be check same with query params get"
 *                  },
 *                  401={
 *                      "description":"bad credential"
 *                  }
 *
 *              }
 *          }
 *      },
 *     "exchangeBilingCost"={
 *          "method"="post",
 *          "controller"=ExchangeBillingToCustomer::class,
 *          "path"="/business_banks/exchnage_billing_cost",
 *          "defaults"={"_api_receive"=false},
 *          "swagger_context"={
 *              "summary"="compelete bank account  ****use vpn *****",
 *              "parameters"={{
 *                  "name"="payload",
 *                  "type"="object",
 *                  "in"="body",
 *                  "properties"={"billing"={"type"="string","example"="/api/billing/1"}}
 *              }},
 *              "responses"={
 *                   200={
 *                      "description":"return a url that user should be redirected to and a id that in success be check same with query params get"
 *                  },
 *                  401={
 *                      "description":"bad credential"
 *                  }
 *
 *              }
 *          }
 *      },
 *      "exchangeAdddUserCost"={
 *          "method"="post",
 *          "controller"=ExchangeCostAddUser::class,
 *          "path"="/business_banks/exchange_adduser_cost",
 *          "defaults"={"_api_receive"=false},
 *          "swagger_context"={
 *              "summary"="compelete bank account  ****use vpn *****",
 *              "parameters"={{
 *                  "name"="payload",
 *                  "type"="object",
 *                  "in"="body",
 *                  "properties"={"userCount"={"type"="number","5"}}
 *              }},
 *              "responses"={
 *                   200={
 *                      "description":"return a url that user should be redirected to and a id that in success be check same with query params get"
 *                  },
 *                  401={
 *                      "description":"bad credential"
 *                  }
 *
 *              }
 *          }
 *      },
*      "payForAdditionalUser"={
 *          "method"="post",
 *          "controller"=AdditionalUserPurchase::class,
 *          "path"="/business_banks/pay_additional_user",
 *          "defaults"={"_api_receive"=false},
 *          "swagger_context"={
 *              "summary"="compelete bank account  ****use vpn *****",
 *              "parameters"={{
 *                  "name"="payload",
 *                  "type"="object",
 *                  "in"="body",
 *                  "properties"={
 *                          "userCount"={"type"="number","5"},
 *                          "amount"={"type"="string","example":"10"},
 *                          "currency"={"type"="string","enum":{"GBP","SEK","DKK","AUD","NZD","CAD","USD","EUR"},"example":"GBP","description"="GBP,SEK,DKK,AUD,NZD,CAD,USD,EUR"}
 *                  }
 *              }},
 *              "responses"={
 *                   200={
 *                      "description":"return a url that user should be redirected to and a id that in success be check same with query params get"
 *                  },
 *                  401={
 *                      "description":"bad credential"
 *                  }
 *
 *              }
 *          }
 *      },
 *     })
 * @ORM\Entity(repositoryClass="App\Repository\BusinessBankRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */

class BusinessBank
{


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business", inversedBy="businessBanks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $business;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=128,nullable=true)
     */
    private $customer;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $mandate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $flowId;

    /**
     * by default cancel so no one can set a bank account without confirm
     * @ORM\Column(type="boolean")
     * @ApiProperty(attributes={"summmary"="if true means this mandate has bean cancel from this customer,on add new mandate ,last ones be canceled"})
     */
    private $cancel=true;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $currency;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBusiness(): ?Business
    {
        return $this->business;
    }

    public function setBusiness(?Business $business): self
    {
        $this->business = $business;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCustomer(): ?string
    {
        return $this->customer;
    }

    public function setCustomer(string $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getMandate(): ?string
    {
        return $this->mandate;
    }

    public function setMandate(?string $mandate): self
    {
        $this->mandate = $mandate;

        return $this;
    }

    public function getFlowId(): ?string
    {
        return $this->flowId;
    }

    public function setFlowId(?string $flowId): self
    {
        $this->flowId = $flowId;

        return $this;
    }

    public function getCancel(): ?bool
    {
        return $this->cancel;
    }

    public function setCancel(bool $cancel): self
    {
        $this->cancel = $cancel;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }
}
